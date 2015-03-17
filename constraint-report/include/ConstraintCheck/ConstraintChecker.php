<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck;

use Wikibase\Repo\WikibaseRepo;
use Wikibase\Repo\Store;
use Wikibase\DataModel\Statement;
use Wikibase\DataModel\Snak;
use Wikibase\Datamodel\Entity;
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
 * Used to start the constraint-check process
 * @package WikidataQuality\ConstraintReport\ConstraintCheck
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

    /**
     * Class for helper functions for constraint checkers.
     * @var ConstraintReportHelper
     */
    private $helper;

    /**
     * Checks Single, Multi and Unique value constraint.
     * @var ValueCountChecker
     */
    private $valueCountChecker;

    /**
     * Checks Qualifier and Qualifiers constraint.
     * @var QualifierChecker
     */
    private $qualifierChecker;

    /**
     * Checks Conflicts with, Item, Target required claim, Symmetric and Inverse constraint.
     * @var ConnectionChecker
     */
    private $connectionChecker;

    /**
     * Checks Type and Value type constraint.
     * @var TypeChecker
     */
    private $typeChecker;

    /**
     * Checks Range and Diff within range constraint.
     * @var RangeChecker
     */
    private $rangeChecker;

    /**
     * Checks Format constraint.
     * @var FormatChecker
     */
    private $formatChecker;

    /**
     * Checks One of constraint.
     * @var OneOfChecker
     */
    private $oneOfChecker;

    /**
     * Checks Commons link constraint.
     * @var CommonsLinkChecker
     */
    private $commonsLinkChecker;

    /**
     * List of all statemtens of given entity.
     * @var StatementList
     */
    private $statements;

    public function __construct() {
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
     * @param Entity\Entity $entity - Entity that shall be checked against constraints
     * @return \Array with results or null
     */
    public function execute( $entity ) {
        if ( $entity ) {

            $this->statements = $entity->getStatements();

            $dbr = wfGetDB( DB_SLAVE );
            $result = array();

            foreach( $this->statements as $statement ) {

                $claim = $statement->getClaim();

                if( $claim->getMainSnak()->getType() === 'value' ) {
                    $dataValue = $claim->getMainSnak()->getDataValue();
                } else {
                    // skip 'somevalue' and 'novalue' cases, todo: handle in a better way
                    continue;
                }

                $propertyId = $claim->getPropertyId();
                $numericPropertyId = $propertyId->getNumericId();

                $res = $dbr->select(
                    'constraints_ready_for_migration',						    // $table
                    array( 'pid', 'constraint_name',
                        'class', 'constraint_status', 'comment', 'group_by', 'item', 'known_exception',
                        'maximum_date', 'maximum_quantity', 'minimum_date', 'minimum_quantity',
                        'namespace', 'pattern', 'property', 'relation' ),		// $vars (columns of the table)
                    ( "pid = $numericPropertyId" ),							    // $conds
                    __METHOD__,													// $fname = 'Database::select',
                    array('')													// $options = array()
                );

                foreach( $res as $row ) {
                    if( in_array( $entity->getId()->getSerialization(), $this->helper->stringToArray( $row->known_exception ) ) ) {
                        $result[] = new CheckResult( $propertyId, $dataValue, $row->constraint_name, array(), 'exception' ); // todo: display parameters anyway
                        continue;
                    }

                    $classArray = $this->helper->stringToArray( $row->class );
                    $itemArray = $this->helper->stringToArray( $row->item );
                    $propertyArray = $this->helper->stringToArray( $row->property );

                    switch( $row->constraint_name ) {
                        // Switch over every constraint, check them accordingly
                        // Return value should be a CheckResult, which should be inserted in an array of CheckResults ($results)
                        // which should be returned in the end

                        // ValueCountCheckers
                        case "Single value":
                            $result[] = $this->getValueCountChecker()
                                ->checkSingleValueConstraint( $propertyId, $dataValue );
                            break;
                        case "Multi value":
                            $result[] = $this->getValueCountChecker()
                                ->checkMultiValueConstraint( $propertyId, $dataValue );
                            break;
                        case "Unique value":
                            $result[] = $this->getValueCountChecker()
                                ->checkUniqueValueConstraint( $propertyId, $dataValue );
                            break;

                        // ConnectionCheckers
                        case "Target required claim":
                            $result[] = $this->getConnectionChecker()
                                ->checkTargetRequiredClaimConstraint( $propertyId, $dataValue, $row->property, $itemArray );
                            break;
                        case "Symmetric":
                            $result[] = $this->getConnectionChecker()
                                ->checkSymmetricConstraint( $propertyId, $dataValue );
                            break;
                        case "Inverse":
                            $result[] = $this->getConnectionChecker()
                                ->checkInverseConstraint( $propertyId, $dataValue, $row->property );
                            break;
                        case "Conflicts with":
                            $result[] = $this->getConnectionChecker()
                                ->checkConflictsWithConstraint( $propertyId, $dataValue, $row->property, $itemArray );
                            break;
                        case "Item":
                            $result[] = $this->getConnectionChecker()
                                ->checkItemConstraint( $propertyId, $dataValue, $row->property, $itemArray );
                            break;

                        // QualifierCheckers
                        case "Qualifier":
                            $result[] = $this->getQualifierChecker()
                                ->checkQualifierConstraint( $propertyId, $dataValue );
                            break;
                        case "Qualifiers":
                            $result[] = $this->getQualifierChecker()
                                ->checkQualifiersConstraint( $propertyId, $dataValue, $statement, $propertyArray );
                            break;

                        // RangeCheckers
                        case "Range":
                            $result[] = $this->getRangeChecker()
                                ->checkRangeConstraint( $propertyId, $dataValue, $row->minimum_quantity, $row->maximum_quantity, $row->minimum_date, $row->maximum_date );
                            break;
                        case "Diff within range":
                            $result[] = $this->getRangeChecker()
                                ->checkDiffWithinRangeConstraint( $propertyId, $dataValue, $row->property, $row->minimum_quantity, $row->maximum_quantity );
                            break;

                        // Type Checkers
                        case "Type":
                            $result[] = $this->getTypeChecker()
                                ->checkTypeConstraint( $propertyId, $dataValue, $this->statements, $classArray, $row->relation );
                            break;
                        case "Value type":
                            $result[] = $this->getTypeChecker()
                                ->checkValueTypeConstraint( $propertyId, $dataValue, $classArray, $row->relation );
                            break;

                        // Rest
                        case "Format":
                            $result[] = $this->getFormatChecker()
                                ->checkFormatConstraint( $propertyId, $dataValue, $row->pattern );
                            break;
                        case "Commons link":
                            $result[] = $this->getCommonsLinkChecker()
                                ->checkCommonsLinkConstraint( $propertyId, $dataValue, $row->namespace );
                            break;
                        case "One of":
                            $result[] = $this->getOneOfChecker()
                                ->checkOneOfConstraint( $propertyId, $dataValue, $itemArray );
                            break;

                        // error case, should not be invoked
                        default:
                            $result[] = new CheckResult( $propertyId, $dataValue, $row->constraint_name, array(), 'error' );
                            break;
                    }

                }

            }
            return $result;
        }
        return null;
    }

    /**
     * @return ValueCountChecker
     */
    private function getValueCountChecker()
    {
        if( !isset( $this->valueCountChecker ) ) {
            $this->valueCountChecker = new ValueCountChecker( $this->statements, $this->helper );
        }
        return $this->valueCountChecker;
    }

    /**
     * @return ConnectionChecker
     */
    private function getConnectionChecker() {
        if( !isset( $this->connectionChecker ) ) {
            $this->connectionChecker = new ConnectionChecker( $this->statements, $this->entityLookup, $this->helper );
        }
        return $this->connectionChecker;
    }

    /**
     * @return QualifierChecker
     */
    private function getQualifierChecker() {
        if( !isset( $this->qualifierChecker ) ) {
            $this->qualifierChecker = new QualifierChecker( $this->statements, $this->helper );
        }
        return $this->qualifierChecker;
    }

    /**
     * @return RangeChecker
     */
    private function getRangeChecker() {
        if( !isset( $this->rangeChecker ) ) {
            $this->rangeChecker = new RangeChecker( $this->statements, $this->helper );
        }
        return $this->rangeChecker;
    }

    /**
     * @return TypeChecker
     */
    private function getTypeChecker() {
        if( !isset( $this->typeChecker ) ) {
            $this->typeChecker = new TypeChecker( $this->entityLookup, $this->helper );
        }
        return $this->typeChecker;
    }

    /**
     * @return OneOfChecker
     */
    private function getOneOfChecker() {
        if( !isset( $this->oneOfChecker ) ) {
            $this->oneOfChecker = new OneOfChecker( $this->helper );
        }
        return $this->oneOfChecker;
    }

    /**
     * @return CommonsLinkChecker
     */
    private function getCommonsLinkChecker() {
        if( !isset( $this->commonsLinkChecker ) ) {
            $this->commonsLinkChecker = new CommonsLinkChecker( $this->statements, $this->helper );
        }
        return $this->commonsLinkChecker;
    }

    /**
     * @return FormatChecker
     */
    private function getFormatChecker() {
        if( !isset( $this->formatChecker ) ) {
            $this->formatChecker = new FormatChecker( $this->helper );
        }
        return $this->formatChecker;
    }

}