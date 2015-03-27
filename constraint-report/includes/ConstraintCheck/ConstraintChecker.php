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

            $result = $this->sortResult( $this->checkEveryStatement( $entity, $dbr ) );
            $this->writeIntoViolationsTable( $dbr, $entity, $result );
            return $result;
        }
        return null;
    }

    private function checkEveryStatement( $entity, $dbr ) {
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
            $claimGuid = $claim->getGuid();
            $res = $this->queryConstraintsForProperty( $dbr, $numericPropertyId );

            foreach( $res as $row ) {
                if( in_array( $entity->getId()->getSerialization(), $this->helper->stringToArray( $row->known_exception ) ) ) {
                    $message = 'This entity is a known exception for this constraint and has been marked as such.';
                    $result[] = new CheckResult( $claimGuid, $propertyId, $dataValue, $row->constraint_name, array(), 'exception', $message ); // todo: display parameters anyway
                    continue;
                }

                $classArray = $this->helper->stringToArray( $row->class );
                $itemArray = $this->helper->stringToArray( $row->item );
                $propertyArray = $this->helper->stringToArray( $row->property );

                $result[] = $this->getCheckResultFor( $claimGuid, $propertyId, $dataValue, $row, $classArray, $itemArray, $propertyArray, $entity, $statement );
            }

        }

        return $result;
    }

    private function getCheckResultFor( $claimGuid, $propertyId, $dataValue, $row, $classArray, $itemArray, $propertyArray, $entity, $statement ) {
        switch( $row->constraint_name ) {
            // Switch over every constraint, check them accordingly.
            // Return value should be a CheckResult, which should be inserted in an array of CheckResults ($results)
            // which should be returned in the end.

            // ValueCountCheckers
            case "Single value":
                return $this->getValueCountChecker()
                    ->checkSingleValueConstraint( $claimGuid, $propertyId, $dataValue );
                break;
            case "Multi value":
                return $this->getValueCountChecker()
                    ->checkMultiValueConstraint( $claimGuid, $propertyId, $dataValue );
                break;
            case "Unique value":
                return $this->getValueCountChecker()
                    ->checkUniqueValueConstraint( $claimGuid, $propertyId, $dataValue );
                break;

            // ConnectionCheckers
            case "Target required claim":
                return $this->getConnectionChecker()
                    ->checkTargetRequiredClaimConstraint( $claimGuid, $propertyId, $dataValue, $row->property, $itemArray );
                break;
            case "Symmetric":
                return $this->getConnectionChecker()
                    ->checkSymmetricConstraint( $claimGuid, $propertyId, $dataValue, $entity->getId()->getSerialization() );
                break;
            case "Inverse":
                return $this->getConnectionChecker()
                    ->checkInverseConstraint( $claimGuid, $propertyId, $dataValue, $entity->getId()->getSerialization(), $row->property );
                break;
            case "Conflicts with":
                return $this->getConnectionChecker()
                    ->checkConflictsWithConstraint( $claimGuid, $propertyId, $dataValue, $row->property, $itemArray );
                break;
            case "Item":
                return $this->getConnectionChecker()
                    ->checkItemConstraint( $claimGuid, $propertyId, $dataValue, $row->property, $itemArray );
                break;

            // QualifierCheckers
            case "Qualifier":
                return $this->getQualifierChecker()
                    ->checkQualifierConstraint( $claimGuid, $propertyId, $dataValue );
                break;
            case "Qualifiers":
                return $this->getQualifierChecker()
                    ->checkQualifiersConstraint( $claimGuid, $propertyId, $dataValue, $statement, $propertyArray );
                break;

            // RangeCheckers
            case "Range":
                return $this->getRangeChecker()
                    ->checkRangeConstraint( $claimGuid, $propertyId, $dataValue, $row->minimum_quantity, $row->maximum_quantity, $row->minimum_date, $row->maximum_date );
                break;
            case "Diff within range":
                return $this->getRangeChecker()
                    ->checkDiffWithinRangeConstraint( $claimGuid, $propertyId, $dataValue, $row->property, $row->minimum_quantity, $row->maximum_quantity );
                break;

            // Type Checkers
            case "Type":
                return $this->getTypeChecker()
                    ->checkTypeConstraint( $claimGuid, $propertyId, $dataValue, $this->statements, $classArray, $row->relation );
                break;
            case "Value type":
                return $this->getTypeChecker()
                    ->checkValueTypeConstraint( $claimGuid, $propertyId, $dataValue, $classArray, $row->relation );
                break;

            // Rest
            case "Format":
                return $this->getFormatChecker()
                    ->checkFormatConstraint( $claimGuid, $propertyId, $dataValue, $row->pattern );
                break;
            case "Commons link":
                return $this->getCommonsLinkChecker()
                    ->checkCommonsLinkConstraint( $claimGuid, $propertyId, $dataValue, $row->namespace );
                break;
            case "One of":
                return $this->getOneOfChecker()
                    ->checkOneOfConstraint( $claimGuid, $propertyId, $dataValue, $itemArray );
                break;

            // error case, should not be invoked
            default:
                return new CheckResult( $propertyId, $dataValue, $row->constraint_name );
                break;
        }
    }

    private function queryConstraintsForProperty( $dbr, $prop ) {
        return $dbr->select(
            'constraints_ready_for_migration',						    // $table
            array( 'pid', 'constraint_name',
                'class', 'constraint_status', 'comment', 'group_by', 'item', 'known_exception',
                'maximum_date', 'maximum_quantity', 'minimum_date', 'minimum_quantity',
                'namespace', 'pattern', 'property', 'relation' ),		// $vars (columns of the table)
            ( "pid = $prop" ),							                // $conds
            __METHOD__,													// $fname = 'Database::select',
            array( '' )													// $options = array()
        );
    }

    private function sortResult( $result ) {
        $sortFunction = function( $a, $b ) {
            $order = array( 'compliance' => 0, 'exception' => 1, 'todo' => 2, 'violation' => 3, 'other' => 4 );

            $statusA = $a->getStatus();
            $statusB = $b->getStatus();

            $orderA = array_key_exists( $statusA, $order ) ? $order[$statusA] : $order['other'];
            $orderB = array_key_exists( $statusB, $order ) ? $order[$statusB] : $order['other'];

            if( $orderA === $orderB ) {
                return 0;
            } else {
                return ( $orderA < $orderB ) ? 1 : -1;
            }
        };

        uasort( $result, $sortFunction );

        return $result;
    }

    /**
     * @return ValueCountChecker
     */
    private function getValueCountChecker() {
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

    /**
     * Takes array of CheckResults, checks, if they already exist in violations table
     * if they do not, write them into it
     * if they do, update the entry
     * @param array $result
     */
    private function writeIntoViolationsTable( $dbr, $entity, $result ) {
        return;
        foreach( $result as $res ) {
            if( $res->getStatus() !== 'violation' ){
                continue;
            }

            if( $this->entryExists( $dbr, $res ) ) {
         //       $this->updateEntry( $dbr, $res );
            } else {
          //      $this->writeEntry( $dbr, $entity, $res );
            }
        }
    }

    /**
     * Checks if entry in violations table already exists
     * @param $db
     * @param $res
     * @return bool
     */
    /* private function entryExists( $db, $res ) {
         $claimGuid = $res->getClaimGuid();
         return !empty(
           /*$db->select(
                 'wdq_violations',
                 array( 'id' ),
                 "claim_guid = $claimGuid"
             )
             $db->select(
                 'wdq_violations',						    // $table
                 array('claim_guid', 'constraint_name' ),		// $vars (columns of the table)
                 ("claim_guid => '$claimGuid'" ),							    // $conds
                 __METHOD__,													// $fname = 'Database::select',
                 array('')													// $options = array()
             )

         );
     }
 */
    /**
     * Inserts row in violation table
     * @param $db
     * @param $entity
     * @param $res
     */
    private function writeEntry( $db, $entity, $res ) {
        $db->insert(
            'wdq_violations',
            array(
                'qid' => $entity->getId()->getSerialization(),
                'pid' => $res->getPropertyId(),
                'claim_guid' => $res->getClaimGuid(),
                'constraint_type_qid' => $res->getConstraintName(),
                'additional_information' => $res->getMessage(),
                'status' => 'violation'
            )
        );
    }

    private function updateEntry( $db, $res ) {
        //todo
    }

}