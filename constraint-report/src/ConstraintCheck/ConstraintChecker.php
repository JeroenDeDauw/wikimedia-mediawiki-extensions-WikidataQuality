<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck;

use Wikibase\Repo\WikibaseRepo;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Repo\Store;
use Wikibase\DataModel\Statement;
use Wikibase\DataModel\Snak;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\ValueCountChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\CommonsLinkChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\ConnectionChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\QualifierChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\RangeChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\TypeChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\FormatChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\OneOfChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;


/**
 * Class ConstraintCheck
 * @package WikidataQuality\ConstraintReport\ConstraintReport
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

    private $helper;

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
        $this->entityLookup = WikibaseRepo::getDefaultInstance()->getEntityLookup();

        // Get load balancer
        wfWaitForSlaves();
        $this->loadBalancer = wfGetLB();

        // Get helper to pass it to every checker
        $this->helper = new ConstraintReportHelper();
    }

    /**
     * Starts the whole constraint-check process.
     * Statements of the entity will be checked against every constraint that is defined on the property.
     * @param \EntityId $entityId - Id of the entity, that should be checked against constraints
     * @return \CompareResultList (adapted version) with results or null
     */
    public function execute( $entityId )
    {
        // Get statements of item
        $entity = $this->entityLookup->getEntity( $this->getEntityID( $entityId ) );
        if ( $entity ) {

            $this->statements = $entity->getStatements();

            $dbr = wfGetDB( DB_SLAVE );
            $result = array();

            foreach( $this->statements as $statement ) {

                $claim = $statement->getClaim();
                $propertyId = $claim->getPropertyId();
                $numericPropertyId = $propertyId->getNumericId();
                $dataValueString = $this->getDataValueString( $claim );

                $res = $dbr->select(
                    'wdq_constraints_from_templates',											                    							                                        // $table
                    array('pid', 'constraint_name', 'base_property', 'class', 'classes', 'exceptions', 'item', 'items', 'list', 'max', 'min', 'pattern', 'property', 'relation', 'values_' ),		// $vars (columns of the table)
                    ("pid = $numericPropertyId"),												                  								                                        // $conds
                    __METHOD__,																	                    							                                        // $fname = 'Database::select',
                    array('')																	                    							                                        // $options = array()
                );

                foreach( $res as $row ) {
                    if( in_array( $entityId, $this->helper->toArray( $row->exceptions ) ) ) {
                        $result[] = new CheckResult( $propertyId, $dataValueString, $row->constraint_name, '\'\'(none)\'\'', 'exception' );
                        continue;
                    }

                    switch( $row->constraint_name ) {
                        // Switch over every constraint, check them accordingly
                        // Return value should be a CheckResult, which should be inserted in an Array of CheckResults ($results)
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
                            $result[] = $this->getConnectionChecker()->checkSymmetricConstraint( $propertyId, $dataValueString, $row->property, $row->item, $row->items );
                            break;
                        case "Symmetric":
                            $result[] = $this->getConnectionChecker()->checkSymmetricConstraint( $propertyId, $dataValueString );
                            break;
                        case "Inverse":
                            $result[] = $this->getConnectionChecker()->checkInverseConstraint( $propertyId, $dataValueString, $row->property );
                            break;
                        case "Conflicts with":
                            $result[] = $this->getConnectionChecker()->checkConflictsWithConstraint( $propertyId, $dataValueString, $row->list );
                            break;
                        case "Item":
                            $result[] = $this->getConnectionChecker()->checkItemConstraint( $propertyId, $dataValueString, $row->property, $row->item, $row->items);
                            break;

                        // QualifierCheckers
                        case "Qualifier":
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
                            $result[] = $this->getRangeChecker()->checkDiffWithinRangeConstraint( $propertyId, $dataValueString, $row->base_property, $row->min, $row->max );
                            break;

                        //Type Checkers
                        case "Type":
                            $result[] = $this->getTypeChecker()->checkTypeConstraint( $propertyId, $dataValueString, $this->statements, $row->class, $row->classes, $row->relation );
                            break;
                        case "Value type":
                            $result[] = $this->getTypeChecker()->checkValueTypeConstraint( $propertyId, $dataValueString, $row->class, $row->classes, $row->relation );
                            break;

                        // Rest
                        case "Format":
                            $result[] = $this->getFormatChecker()->checkFormatConstraint( $propertyId, $dataValueString, $row->pattern );
                            break;
                        case "Commons link":
                            $result[] = $this->getCommonsLinkChecker()->checkCommonsLinkConstraint( $propertyId, $dataValueString );
                            break;
                        case "One of":
                            $result[] = $this->getOneOfChecker()->checkOneOfConstraint( $propertyId, $dataValueString, $row->values_ );
                            break;
                        default:
                            //not yet implemented cases, also error case SHOULD NOT BE INVOKED
                            $result[] = new CheckResult( $propertyId, $dataValueString, $row->constraint_name, '\'\'(none)\'\'', "wtf" );
                            break;
                    }

                }

            }
            return $result;
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
            $this->valueCountChecker = new ValueCountChecker( $this->statements, $this->helper );
        }
        return $this->valueCountChecker;
    }

    private function getConnectionChecker()
    {
        if( !isset( $this->connectionChecker ) ) {
            $this->connectionChecker = new ConnectionChecker( $this->statements, $this->entityLookup, $this->helper );
        }
        return $this->connectionChecker;
    }

    private function getQualifierChecker()
    {
        if( !isset( $this->qualifierChecker ) ) {
            $this->qualifierChecker = new QualifierChecker( $this->statements, $this->helper );
        }
        return $this->qualifierChecker;
    }

    private function getRangeChecker()
    {
        if( !isset( $this->rangeChecker ) ) {
            $this->rangeChecker = new RangeChecker( $this->statements, $this->helper);
        }
        return $this->rangeChecker;
    }

    private function getTypeChecker()
    {
        if( !isset( $this->typeChecker ) ) {
            $this->typeChecker = new TypeChecker( $this->statements, $this->entityLookup, $this->helper );
        }
        return $this->typeChecker;
    }

    private function getOneOfChecker()
    {
        if( !isset( $this->oneOfChecker ) ) {
            $this->oneOfChecker = new OneOfChecker( $this->helper );
        }
        return $this->oneOfChecker;
    }

    private function getCommonsLinkChecker()
    {
        if( !isset( $this->commonsLinkChecker ) ) {
            $this->commonsLinkChecker = new CommonsLinkChecker( $this->statements, $this->helper );
        }
        return $this->commonsLinkChecker;
    }


    private function getFormatChecker()
    {
        if( !isset( $this->formatChecker ) ) {
            $this->formatChecker = new FormatChecker( $this->helper );
        }
        return $this->formatChecker;
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


}