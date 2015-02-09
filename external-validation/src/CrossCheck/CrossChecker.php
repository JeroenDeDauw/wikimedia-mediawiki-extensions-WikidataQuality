<?php

namespace WikidataQuality\ExternalValidation\CrossCheck;


use Wikibase\Repo\WikibaseRepo;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;
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
        $this->entityLookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();

        // Get load balancer
        wfWaitForSlaves();
        $this->loadBalancer = wfGetLB();

        // Include mapping
        require( "mapping.inc.php" );
        $this->mapping = $mapping;
    }

    /**
     * Starts the whole cross-check process.
     * Statements of the item will be checked against each external database, that is supported and linked by the item.
     * @param \ItemId $itemId - Id of the item, that should be cross-cheked
     * @return \CompareResultList
     */
    public function execute( $itemId )
    {
        // Get statements of item
        $item = $this->entityLookup->getEntity( $itemId );
        if ( $item ) {
            $statements = $item->getStatements();

            // Check statements for validating identifier properties
            $results = new CompareResultList();
            foreach ( $statements as $statement ) {
                $propertyId = $statement->getClaim()->getPropertyId();
                if ( array_key_exists( $propertyId->getNumericId(), $this->mapping ) ) {
                    // Run cross-check for this database
                    $results->merge( $this->crossCheckStatements( $statements, $propertyId ) );
                }
            }

            return $results;
        }
    }

    /**
     * Checks given statements against one single database identified by given property id.
     * @param \StatementList $statements - list of statements, that should be cross-checked
     * @param \PropertyId $identifierPropertyId - id of the identifier property, that represents the external database
     * @return \CompareResultList
     */
    private function crossCheckStatements( $statements, $identifierPropertyId )
    {
        // Get mapping for current database
        $currentMapping = $this->mapping[ $identifierPropertyId->getNumericId() ];

        // Filter out statements, that can not be checked against the current database
        $validateableStatements = new StatementList();
        foreach ( $statements as $statement ) {
            $propertyId = $statement->getClaim()->getPropertyId();
            if ( array_key_exists( $propertyId->getNumericId(), $currentMapping ) ) {
                $validateableStatements->addStatement( $statement );
            }
        }

        // Get referenced external id(s) for the current database
        $externalIds = array();
        $snaks = $statements->getWithPropertyId( $identifierPropertyId )->getMainSnaks();
        foreach ( $snaks as $snak ) {
            if ( $snak instanceof PropertyValueSnak ) {
                $externalIds[ ] = $snak->getDataValue()->getValue();
            }
        }

        // Compare wikidata statements with each linked external entity of the current database
        $results = new CompareResultList();
        foreach ( $externalIds as $externalId ) {
            // Get external entity
            $externalEntity = $this->getExternalEntity( $identifierPropertyId, $externalId );
            if ( $externalEntity ) {
                // Compare each validatable statement
                foreach ( $validateableStatements as $validateableStatement ) {
                    // Get claim and ids
                    $claim = $validateableStatement->getClaim();
                    $claimGuid = $claim->getGuid();

                    // Get main snak
                    $mainSnak = $claim->getMainSnak();
                    if ( $mainSnak instanceof PropertyValueSnak ) {
                        $dataValue = $mainSnak->getDataValue();
                        $propertyId = $mainSnak->getPropertyId();
                        $propertyMapping = $currentMapping[ $propertyId->getNumericId() ];

                        $result = $this->compareDataValue( $propertyId, $claimGuid, $dataValue, $externalEntity, $propertyMapping );
                        if ( $result ) {
                            $results->add( $result );
                        }
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Retrieves external entity by its id from database.
     * @param \PropertyId $identifierPropertyId - id of the identifier property, that represents the external database
     * @param string $externalId - id of the external entity
     */
    private function getExternalEntity( $identifierPropertyId, $externalId )
    {
        // Connect to database
        $db = $this->loadBalancer->getConnection( DB_SLAVE );

        // Run query
        $numericPropertyId = $identifierPropertyId->getNumericId();
        $result = $db->selectRow( DUMP_DATA_TABLE, array( "dump_id", "external_data" ), array( "pid=$numericPropertyId", "external_id=\"$externalId\"" ) );
        if ( $result !== false ) {
            $this->dumpMetaInformation = $this->getMetaInformation( $db, $result->dump_id );
            return $result->external_data;
        }
    }

    /**
     * Retrieves meta information by dump id from database.
     * @param $db - loadBalancer connection
     * @param int $dumpId - id of the dump
     * @return \DumpMetaInformation
     */
    private function getMetaInformation( $db, $dumpId )
    {
        // Run query
        $result = $db->selectRow( DUMP_META_TABLE, array( "format", "language", "date_format", "name" ), array( "row_id=$dumpId" ) );
        if ( $result !== false ) {
            $format = $result->format;
            $language = $result->language;
            $dateFormat = $result->date_format;
            $dataSourceName = $result->name;
            return new DumpMetaInformation( $format, $language, $dateFormat, $dataSourceName );
        }
    }

    /**
     * Compares a single DataValue object with a external entity by evaluating the property mapping.
     * @param $dataValue
     * @param $externalEntity
     * @param $propertyMapping
     * @return \CompareResult
     */
    private function compareDataValue( $propertyId, $claimGuid, $dataValue, $externalEntity, $propertyMapping )
    {
        // Get external values by evaluating mapping
        $mapingEvaluator = MappingEvaluator::getEvaluator( $this->dumpMetaInformation->getFormat(), $externalEntity );
        if ( $mapingEvaluator ) {
            $nodeSelector = $propertyMapping[ "nodeSelector" ];
            $valueFormatter = array_key_exists( "valueFormatter", $propertyMapping ) ? $propertyMapping[ "valueFormatter" ] : null;
            $externalValues = $mapingEvaluator->evaluate( $nodeSelector, $valueFormatter );

            // Start comparer if external value could be evaluated
            if ( count( $externalValues ) > 0 ) {
                $comparer = DataValueComparer::getComparer( $this->dumpMetaInformation, $dataValue, $externalValues );
                if ( $comparer ) {
                    $result = $comparer->execute();

                    if ( isset( $result ) ) {
                        return new CompareResult( $propertyId, $claimGuid, $comparer->localValues, $comparer->externalValues, !$result, null, $this->dumpMetaInformation->getDataSourceName() );
                    }
                }
            }
        }
    }
}