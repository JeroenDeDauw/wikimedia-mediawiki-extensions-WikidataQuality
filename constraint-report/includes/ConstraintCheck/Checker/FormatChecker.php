<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

/**
 * Class FormatChecker.
 * Checks 'Format' constraint.
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Checker
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class FormatChecker {

    /**
     * Class for helper functions for constraint checkers.
     * @var ConstraintReportHelper
     */
    private $helper;

    /**
     * @param ConstraintReportHelper $helper
     */
    public function __construct( $helper ) {
        $this->helper = $helper;
    }

    /**
     * Checks 'Format' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param string $pattern
     * @return CheckResult
     */
    public function checkFormatConstraint( $propertyId, $dataValue, $pattern ) {
        $parameters = array();

        if( $pattern === null ) {
            $parameters['pattern'] = array( 'null' );
        } else {
            $parameters['pattern'] = array( $pattern );
        }

        /*
         * error handling:
         *   type of $dataValue for properties with 'Format' constraint has to be 'string'
         *   parameter $pattern must not be null
         */
        if( $dataValue->getType() !== 'string' || $pattern === null ) {
            return new CheckResult( $propertyId, $dataValue, 'Format', $parameters, 'error' );
        }

        $comparativeString = $dataValue->getValue();

        $status = preg_match( '/^' . str_replace( '/', '\/', $pattern ) . '$/', $comparativeString  ) ? 'compliance' : 'violation';

        return new CheckResult( $propertyId, $dataValue, 'Format', $parameters, $status );
    }

}