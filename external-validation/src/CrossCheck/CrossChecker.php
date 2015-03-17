<?php

namespace WikidataQuality\ExternalValidation\CrossCheck;


use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\Repo\WikibaseRepo;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResultList;


/**
 * Class CrossChecker
 * @package WikidataQuality\ExternalValidation\CrossCheck
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CrossChecker
{
    /**
     * Wikibase entity lookup.
     * @var \Wikibase\Lib\Store\EntityLookup
     */
    private $entityLookup;

    /**
     * Wikibase entity id parser.
     * @var \Wikibase\DataModel\Entity\EntityIdParser
     */
    private $entityIdParser;

    /**
     * Wikibase load balancer for database connections.
     * @var \LoadBalancer
     */
    private $loadBalancer;

    /**
     * Metadata for dump belonging to external entity.
     * @var array
     */
    private $dumpMetaInformation;


    public function __construct()
    {
        // Get entity lookup
        $this->entityLookup = WikibaseRepo::getDefaultInstance()->getEntityLookup();

        // Get entity id parser
        $this->entityIdParser = WikibaseRepo::getDefaultInstance()->getEntityIdParser();

        // Get load balancer
        wfWaitForSlaves();
        $this->loadBalancer = wfGetLB();
    }


    /**
     * Runs cross-check for all statements of a given entity.
     * @param \EntityId $entity - Id of the entity that should be cross-checked.
     * @param \PropertyId|array|null $propertyIds - If specified, only statements with given property ids will be cross-checked.
     * @return CompareResultList
     * @throws InvalidArgumentException
     */
    public function crossCheckEntity( $entity, $propertyIds = array() )
    {
        if ( $entity ) {
            // Get statements to be cross-checked
            $statements = new StatementList();
            if ( $propertyIds ) {
                if ( $propertyIds instanceof PropertyId ) {
                    $propertyIds = array( $propertyIds );
                }
                if ( is_array( $propertyIds ) || $propertyIds instanceof Traversable ) {
                    foreach ( $propertyIds as $propertyId ) {
                        if ( $propertyId instanceof PropertyId ) {
                            foreach ( $entity->getStatements()->getWithPropertyId( $propertyId ) as $statement ) {
                                $statements->addStatement( $statement );
                            }
                        } else {
                            throw new InvalidArgumentException( 'Every element in $propertyIds must be an instance of PropertyId.' );
                        }
                    }
                } else {
                    throw new InvalidArgumentException( '$propertyIds must be a PropertyId, array or an instance of Traversable.' );
                }
            } else {
                $statements = $entity->getStatements();
            }

            // Run cross-check for filtered statements
            return $this->crossCheckStatements( $entity, $statements );
        }
    }

    /**
     * Runs cross-check for specific statements of the given entity.
     * @param \EntityId $entity - Id of the entity that should be cross-checked.
     * @param \Statement|\StatementList $statements - Statements of the given entity that should be cross-checked.
     * @return CompareResultList
     * @throws InvalidArgumentException
     */
    public function crossCheckStatements( $entity, $statements )
    {
        // Check $statements argument
        if ( $statements instanceof Statement ) {
            $statements = new StatementList( $statements );
        } elseif ( !( $statements instanceof StatementList ) ) {
            throw new InvalidArgumentException( '$statements must be Statement or StatementList.' );
        }

        // Connect to database
        $db = $this->loadBalancer->getConnection( DB_SLAVE );

        // Get validatable properties
        $validatableProperties = $this->getValidatablePropertyIds( $db );

        // Run cross-check for each external id
        $resultList = new CompareResultList();
        foreach ( $validatableProperties as $identifierPropertyId => $validateablePropertyIds ) {
            // Parse property id from array key
            $identifierPropertyId = new PropertyId( 'P' . $identifierPropertyId );

            // Cross-check validateable statements for single external database
            $resultList->merge( $this->crossCheckStatementsWithDatabase( $db, $statements, $identifierPropertyId, $validateablePropertyIds ) );
        }

        return $resultList;
    }

    /**
     * Checks given statements against one single database identified by given property id.
     * @param \StatementList $statements - List of statements, that should be cross-checked
     * @param \PropertyId $identifierPropertyId - Id of the identifier property, that represents the external database
     * @param string $externalId - Id of the external entity, that is equivalent to the wikibase entity.
     * @return \CompareResultList
     */
    private function crossCheckStatementsWithDatabase( $db, $statements, $identifierPropertyId, $validateablePropertyIds )
    {
        // Get external ids
        $externalIds = array();
        $identifierStatements = $statements->getWithPropertyId( $identifierPropertyId );
        foreach ( $identifierStatements as $identifierStatement ) {
            $mainSnak = $identifierStatement->getClaim()->getMainSnak();
            if ( $mainSnak instanceof PropertyValueSnak ) {
                $externalIds[] = $mainSnak->getDataValue()->getValue();
            }
        }

        // Cross-check validateable statements
        $results = new CompareResultList();
        foreach ( $statements as $statement ) {
            // Check, if statements is validateable with current external database
            if( in_array( $statement->getClaim()->getPropertyId()->getNumericId(), $validateablePropertyIds ))
            {
                // Get claim with guid
                $claim = $statement->getClaim();
                $claimGuid = $claim->getGuid();

                // Get main snak
                $mainSnak = $claim->getMainSnak();
                if ( $mainSnak instanceof PropertyValueSnak ) {
                    $dataValue = $mainSnak->getDataValue();
                    $propertyId = $mainSnak->getPropertyId();

                    // Get external values for propertyId
                    $externalValues = $this->getExternalValues( $db, $identifierPropertyId, $externalIds, $propertyId );

                    // Compare data value
                    $result = $this->compareDataValues( $propertyId, $claimGuid, $dataValue, $externalValues );
                    if ( $result ) {
                        $results->add( $result );
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Retrieves external entity by its id from database.
     * @param \PropertyId $identifierPropertyId - Id of the identifier property, that represents the external database
     * @param string $externalIds - Ids of the external entity
     * @param \PropertyId $propertyId - Id of the property for which the external values are needed
     * @return array
     */
    private function getExternalValues( $db, $identifierPropertyId, $externalIds, $propertyId )
    {
        // Build external ids conditions
        $externalIdsConditions = array();
        foreach ( $externalIds as $externalId ) {
            $externalIdsConditions[] = "external_id=\"$externalId\"";
        }

        // Run query
        $numericIdentifierPropertyId = $identifierPropertyId->getNumericId();
        $numericPropertyId = $propertyId->getNumericId();
        $result = $db->select(
            DUMP_DATA_TABLE,
            array( 'dump_id', 'external_value' ),
            array(
                "identifier_pid=$numericIdentifierPropertyId",
                "pid=$numericPropertyId",
                implode( ' OR ', $externalIdsConditions )
            )
        );

        if ( $result !== false ) {
            $externalValues = array();
            foreach ($result as $row) {
                $externalValues[] = $row->external_value;
                $dumpId = $row->dump_id;
            }
            // TODO: Maybe there are multiple dumps per identifier property
            if( isset( $dumpId ) ) {
                $this->dumpMetaInformation = DumpMetaInformation::get( $db, $dumpId );
            }
            return $externalValues;
        }
        return null;
    }

    /**
     * Compares a single DataValue object with a external entity by evaluating the property mapping.
     * @param $propertyId - PropertyId of the claim, that contains the DataValue
     * @param $claimGuid - Guid of the claim, that contains the DataValue
     * @param $dataValue - DataValue, that should be compared
     * @param $externalValues - External entity, that should be used to check against
     * @return \CompareResult
     */
    private function compareDataValues( $propertyId, $claimGuid, $dataValue, $externalValues )
    {
        // Start comparer if external value could be evaluated
        if ( count( $externalValues ) > 0 ) {
            $comparer = DataValueComparer::getComparer( $this->dumpMetaInformation, $dataValue, $externalValues );
            if ( $comparer ) {
                $result = $comparer->execute();

                if ( isset( $result ) ) {
                    return new CompareResult( $propertyId, $claimGuid, $comparer->getLocalValue(), $comparer->getExternalValues(), !$result, null, $this->dumpMetaInformation );
                }
            }
        }
        return null;
    }

    /*
     * Returns ids of those properties, which can be validated.
     * @param DatabaseBase $db
     */
    private function getValidatablePropertyIds( $db )
    {
        $result = $db->select(
            DUMP_DATA_TABLE,
            array( 'identifier_pid', 'pid' ),
            array(),
            __METHOD__,
            array( 'DISTINCT' )
        );
        if( $result )
        {
            $validatableProperties = array();
            foreach ( $result as $row ) {
                $validatableProperties[ $row->identifier_pid ][] = $row->pid;
            }

            return $validatableProperties;
        }
    }
}