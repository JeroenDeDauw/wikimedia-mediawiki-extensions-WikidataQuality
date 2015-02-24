<?php

namespace WikidataQuality\ExternalValidation\CrossCheck;


use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\MappingEvaluator;
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
     * Mapping that is used to extract correct data from external entities.
     * @var array
     */
    private $mapping;

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

        // Include mapping
        require( 'mapping.inc.php' );
        $this->mapping = $mapping;
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

        // Extract external ids
        $externalIds = $this->extractExternalIds( $entity );

        // Run cross-check for each external id
        $resultList = new CompareResultList();
        foreach ( $externalIds as $identifierPropertyId => $externalIdsPerDb ) {
            // Parse property id from array key
            $identifierPropertyId = new PropertyId( $identifierPropertyId );

            foreach ( $externalIdsPerDb as $externalId ) {
                $resultList->merge( $this->crossCheckStatementsWithDatabase( $statements, $identifierPropertyId, $externalId ) );
            }
        }

        return $resultList;
    }

    /**
     * Extracts all external ids from an entity, that are supported for cross-checks.
     * @param \Entity $entity - Entity from which external ids should be extracted
     * @return array
     */
    private function extractExternalIds( $entity )
    {
        $externalIds = array();
        foreach ( $entity->getStatements() as $statement ) {
            $propertyId = $statement->getClaim()->getPropertyId();
            if ( array_key_exists( $propertyId->getNumericId(), $this->mapping ) ) {
                $mainSnak = $statement->getClaim()->getMainSnak();
                if ( $mainSnak instanceof PropertyValueSnak ) {
                    $externalIds[ (string)$propertyId ][ ] = $mainSnak->getDataValue()->getValue();
                }
            }
        }

        return $externalIds;
    }

    /**
     * Checks given statements against one single database identified by given property id.
     * @param \StatementList $statements - List of statements, that should be cross-checked
     * @param \PropertyId $identifierPropertyId - Id of the identifier property, that represents the external database
     * @param string $externalId - Id of the external entity, that is equivalent to the wikibase entity.
     * @return \CompareResultList
     */
    private function crossCheckStatementsWithDatabase( $statements, $identifierPropertyId, $externalId )
    {
        // Get mapping for current database
        $mapping = $this->mapping[ $identifierPropertyId->getNumericId() ];

        // Filter out statements, that can not be checked against the current database
        $validateableStatements = $this->getValidateableStatements( $statements, $mapping );

        // Get external entity
        $externalEntity = $this->getExternalEntity( $identifierPropertyId, $externalId );

        // If an external entity exists, Compare each validatable statement
        $results = new CompareResultList();
        if ( $externalEntity ) {
            foreach ( $validateableStatements as $validateableStatement ) {
                // Get claim with guid
                $claim = $validateableStatement->getClaim();
                $claimGuid = $claim->getGuid();

                // Get main snak
                $mainSnak = $claim->getMainSnak();
                if ( $mainSnak instanceof PropertyValueSnak ) {
                    $dataValue = $mainSnak->getDataValue();
                    $propertyId = $mainSnak->getPropertyId();
                    $propertyMapping = $mapping[ $propertyId->getNumericId() ];

                    // Compare data value
                    $result = $this->compareDataValue( $propertyId, $claimGuid, $dataValue, $externalEntity, $propertyMapping );
                    if ( $result ) {
                        $results->add( $result );
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Filter out those statements, that can not be cross-checked against a certain database.
     * @param \StatementList $statements - Source list of statements
     * @param array $mapping - Mapping for the database that is used for cross-checking
     * @return StatementList
     */
    private function getValidateableStatements( $statements, $mapping )
    {
        $validateableStatements = new StatementList();
        $validateablePropertyIds = array_keys( $mapping );

        foreach ( $statements as $statement ) {
            $propertyId = $statement->getClaim()->getPropertyId();
            if ( in_array( $propertyId->getNumericId(), $validateablePropertyIds ) ) {
                $validateableStatements->addStatement( $statement );
            }
        }

        return $validateableStatements;
    }

    /**
     * Retrieves external entity by its id from database.
     * @param \PropertyId $identifierPropertyId - Id of the identifier property, that represents the external database
     * @param string $externalId - Id of the external entity
     * @return string
     */
    private function getExternalEntity( $identifierPropertyId, $externalId )
    {
        // Connect to database
        $db = $this->loadBalancer->getConnection( DB_SLAVE );

        // Run query
        $numericPropertyId = $identifierPropertyId->getNumericId();
        $result = $db->selectRow( DUMP_DATA_TABLE, array( 'dump_id', 'external_data' ), array( "pid=$numericPropertyId", "external_id=\"$externalId\"" ) );
        if ( $result !== false ) {
            $this->dumpMetaInformation = $this->getMetaInformation( $db, $result->dump_id );
            return $result->external_data;
        }
        return null;
    }

    /**
     * Retrieves meta information by dump id from database.
     * @param $db - Database connection
     * @param int $dumpId - Id of the dump
     * @return \DumpMetaInformation
     */
    private function getMetaInformation( $db, $dumpId )
    {
        // Run query
        $result = $db->selectRow( DUMP_META_TABLE, array( 'format', 'language', 'date_format', 'name' ), array( "row_id=$dumpId" ) );
        if ( $result !== false ) {
            $format = $result->format;
            $language = $result->language;
            $dateFormat = $result->date_format;
            $dataSourceName = $result->name;
            return new DumpMetaInformation( $format, $language, $dateFormat, $dataSourceName );
        }
        return null;
    }

    /**
     * Compares a single DataValue object with a external entity by evaluating the property mapping.
     * @param $propertyId - PropertyId of the claim, that contains the DataValue
     * @param $claimGuid - Guid of the claim, that contains the DataValue
     * @param $dataValue - DataValue, that should be compared
     * @param $externalEntity - External entity, that should be used to check against
     * @param $propertyMapping - Property mapping, that should be used for cross-checking
     * @return \CompareResult
     */
    private function compareDataValue( $propertyId, $claimGuid, $dataValue, $externalEntity, $propertyMapping )
    {
        // Get external values by evaluating mapping
        $mapingEvaluator = MappingEvaluator::getEvaluator( $this->dumpMetaInformation->getFormat(), $externalEntity );
        if ( $mapingEvaluator ) {
            $nodeSelector = $propertyMapping[ 'nodeSelector' ];
            $valueFormatter = array_key_exists( 'valueFormatter', $propertyMapping ) ? $propertyMapping[ 'valueFormatter' ] : null;
            $externalValues = $mapingEvaluator->evaluate( $nodeSelector, $valueFormatter );

            // Start comparer if external value could be evaluated
            if ( count( $externalValues ) > 0 ) {
                $comparer = DataValueComparer::getComparer( $this->dumpMetaInformation, $dataValue, $externalValues );
                if ( $comparer ) {
                    $result = $comparer->execute();

                    if ( isset( $result ) ) {
                        return new CompareResult( $propertyId, $claimGuid, $comparer->getLocalValues(), $comparer->getExternalValues(), !$result, null, $this->dumpMetaInformation->getDataSourceName() );
                    }
                }
            }
        }
        return null;
    }
}