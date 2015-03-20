<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Class QualifierChecker.
 * Checks 'Qualifier' and 'Qualifiers' constraint.
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Checker
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class QualifierChecker {

    /**
     * List of all statemtens of given entity.
     * @var StatementList
     */
    private $statements;

    /**
     * Class for helper functions for constraint checkers.
     * @var ConstraintReportHelper
     */
    private $helper;

    /**
     * @param StatementList $statements
     * @param ConstraintReportHelper $helper
     */
    public function __construct( $statements, $helper ) {
        $this->statements = $statements;
        $this->helper = $helper;
    }

    /**
     * If this method gets invoked, it is automatically a violation since this method only gets invoked
     * for properties used in statements.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @return CheckResult
     */
    public function checkQualifierConstraint( $propertyId, $dataValue ) {
        return new CheckResult( $propertyId, $dataValue, 'Qualifier', array(), 'violation' );
    }

    /**
     * Checks 'Qualifiers' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param Statement $statement
     * @param array $propertyArray
     * @return CheckResult
     */
    public function checkQualifiersConstraint( $propertyId, $dataValue, $statement, $propertyArray ) {
        $parameters = array();

        if( empty( $propertyArray ) ) {
            $parameters['property'] = array( 'null' );
        } else {
            $func = function( $property ) {
                return new PropertyId( $property );
            };
            $parameters['property'] = array_map( $func, $propertyArray );
        }

        /*
         * error handling:
         *  parameter $propertyArray can be null, meaning that there are explicitly no qualifiers allowed
         */

        $status = 'compliance';

        foreach( $statement->getQualifiers() as $qualifier ) {
            $pid = $qualifier->getPropertyId()->getSerialization();
            if( !in_array( $pid, $propertyArray ) ){
                $status = 'violation';
                break;
            }
        }

        return new CheckResult( $propertyId, $dataValue, 'Qualifiers', $parameters, $status );
    }

}