<?php

namespace WikidataQuality\ConstraintChecker;

use Wikibase\Repo\WikibaseRepo;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Snak\PropertyValueSnak;


/**
 * Class ConstraintChecker
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class ConstraintChecker {

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


    private $valueCountChecker;
    private $qualifierChecker;
    private $connectionChecker;
    private $typeChecker;
    private $rangeChecker;
    private $formatChecker;
    private $oneOfChecker;
    private $commonsLinkChecker;
    private $itemChecker;

    private $statements;


    public function __construct()
    {
        // Get entity lookup
        $this->entityLookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();

        // Get load balancer
        wfWaitForSlaves();
        $this->loadBalancer = wfGetLB();
    }

    /**
     * Starts the whole constraint-check process.
     * Statements of the entity will be checked against every constraint that is defined on the property.
     * @param \EntityId $entityId - Id of the entity, that should be checked against constraints
     * @return \CompareResultList (adapted version) with results or null
     * TODO adapt CompareResultList
     */
    public function execute( $entityId )
    {
        // Get statements of item
        $entity = $this->entityLookup->getEntity( $this->getEntityID( $entityId ) );
        if ( $entity ) {

            $this->statements = $entity->getStatements();

            // only call this function, when you really need the property count (lazy initialization)
            $propertyCount = $this->getPropertyCount( $this->statements );

            $dbr = wfGetDB( DB_SLAVE );

            foreach( $this->statements as $statement ) {

                $claim = $statement->getClaim();
                $propertyId = $claim->getPropertyId();
                $numericPropertyId = $propertyId->getNumericId();
                $dataValueString = $this->getDataValueString( $claim );

                $res = $dbr->select(
                    'wdq_constraints_from_templates',											                    							// $table
                    array('pid', 'constraint_name', 'base_property', 'exceptions', 'item', 'items', 'max', 'min', 'property', 'values_' ),		// $vars (columns of the table)
                    ("pid = $numericPropertyId"),												                  								// $conds
                    __METHOD__,																	                    							// $fname = 'Database::select',
                    array('')																	                    							// $options = array()
                );

                $result = array();

                foreach( $res as $row ) {

                    switch( $row->constraint_name ) {
                        // Switch over every constraint, check them accordingly
                        // Return value should be a CheckResult, which should be inserted in an Array of CheckResults (§results)
                        // which should be returned in the end

                        // ValueCountCheckers
                        case "Single value":
                            $result[] = $this->getValueCountChecker()->checkSingleValueConstraint( $propertyId, $dataValueString );
                            break;
                        case "Multi value":
                            $result[] = $this->getValueCountChecker()->checkMultiValueConstraint( $propertyId, $dataValueString );
                            break;
                        case "Unique value": // todo
                            $result[] = $this->getValueCountChecker()->checkUniqueValueConstraint( $propertyId, $dataValueString );
                            break;

                        // ConnectionCheckers
                        case "Target required claim":
                            $result[] = $this->getConnectionChecker()->checkSymmetricConstraint( $propertyId, $dataValueString, $row->property, $row->$item, $row->$items );
                            break;
                        case "Symmetric":
                            $result[] = $this->getConnectionChecker()->checkSymmetricConstraint( $propertyId, $dataValueString );
                            break;
                        case "Inverse":
                            $result[] = $this->getConnectionChecker()->checkInverseConstraint( $propertyId, $dataValueString, $row->property );
                            break;
                        case "Conflicts with": // todo
                            $result[] = $this->getConnectionChecker()->checkConflictsWithConstraint( $propertyId, $dataValueString );
                            break;

                        // QualifierCheckers
                        case "Qualifier": // todo
                            $result[] = $this->getQualifierChecker()->checkQualifierConstraint( $propertyId, $dataValueString );
                            break;
                        case "Qualifiers": // todo
                            $result[] = $this->getQualifierChecker()->checkQualifiersConstraint( $propertyId, $dataValueString );
                            break;

                        // RangeCheckers
                        case "Range":
                            $result[] = $this->getRangeChecker()->checkRangeConstraint( $propertyId, $dataValueString, $row->min, $row->max );
                            break;
                        case "Diff within range":
                            $result[] = $this->getRangeChecker()->checkDiffWithinRangeConstraint( $propertyId, $dataValueString, $row->basePropertyId, $row->min, $row->max );
                            break;

                        // Rest

                        default:
                            //not yet implemented cases, also error case
                            $result[] = new \CheckResult( $propertyId, $dataValueString, $row->constraint_name, "", "todo" );
                            break;
                    }

                }

            }

        }
        return null;
    }

    private function getEntityID($entityId)
    {
        switch(strtoupper($entityId[0])) {
            case 'Q':
                return new ItemId($entityId);
            case 'P':
                return new PropertyId($entityId);
            default:
                return null;
        }
    }


    private function getValueCountChecker()
    {
        if( !isset( $this->valueCountChecker ) ) {
            $this->valueCountChecker = new ValueCountChecker( $this->statements );
        }
        return $this->valueCountChecker;
    }

    private function getConnectionChecker()
    {
        if( !isset( $this->connectionChecker ) ) {
            $this->connectionChecker = new ConnectionChecker( $this->statements, $this->entityLookup );
        }
        return $this->connectionChecker;
    }

    private function getQualifierChecker()
    {
        if( !isset( $this->qualifierChecker ) ) {
            $this->qualifierChecker = new QualifierChecker( $this->statements );
        }
        return $this->qualifierChecker;
    }

    private function getRangeChecker()
    {
        if( !isset( $this->rangeChecker ) ) {
            $this->rangeChecker = new RangeChecker( $this->statements);
        }
        return $this->qualifierChecker;
    }

    /**
     * @param $getDataValue
     * @return mixed|string
     */
    private function dataValueToString($dataValue)
    {
        $dataValueType = $dataValue->getType();
        switch( $dataValueType ) {
            case 'string':
            case 'decimal':
            case 'number':
            case 'boolean':
            case 'unknown':
                return $dataValue->getValue();
            case 'quantity':
                return $dataValue->getAmount()->getValue();
            case 'time':
                return $dataValue->getTime();
            case 'globecoordinate':
            case 'geocoordinate':
                return 'Latitude: ' . $dataValue->getLatitude() . ', Longitude: ' . $dataValue->getLongitude();
            case 'monolingualtext':
                return $dataValue->getText();
            case 'multilingualtext':
                return array_key_exists('en', $dataValue) ? $dataValue->getTexts()['en'] : array_shift($dataValue->getTexts());;
            case 'wikibase-entityid':
                return $dataValue->getEntityId();
            case 'bad':
            default:
                return null;
                //error case
        }
    }

    /**
     * @param $claim
     */
    private function getDataValueString($claim)
    {
        $mainSnak = $claim->getMainSnak();
        if( $mainSnak->getType() == 'value' ) {
            return $this->dataValueToString( $mainSnak->getDataValue() );
        } else {
            return '\'\'(' . $mainSnak->getType() . '\'\')';
        }
    }

    function checkOneOfConstraint( $propertyId, $dataValueString, $values ) {
        $allowedValues = $this->convertStringFromTemplatesToArray( $values );

        if( !in_array($dataValueString, $allowedValues) ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        $showMax = 5;
        if( sizeof($allowedValues) <= $showMax ) {
            $parameterString = 'values: ' . implode(", ", $allowedValues);
        } else {
            $parameterString = 'values: ' . implode(", ", array_slice($allowedValues, 0, $showMax)) . ' \'\'(and ' . (sizeof($allowedValues)-$showMax) . ' more)\'\'';
        }

        return new \CheckResult($propertyId, $dataValueString, "One of", $parameterString, $status );
    }


}