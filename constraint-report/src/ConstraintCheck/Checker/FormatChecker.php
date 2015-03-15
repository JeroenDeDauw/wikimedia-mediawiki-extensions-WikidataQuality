<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class FormatChecker {

    private $helper;

    public function __construct( $helper ) {
        $this->helper = $helper;
    }

    public function checkFormatConstraint( $propertyId, $dataValue, $pattern ) {
        $parameters = array();

        if( $pattern == null ) {
            $parameters['pattern'] = array( 'null' );
        } else {
            $parameters['pattern'] = array( $pattern );
        }

        /*
         * error handling:
         *   type of $dataValue for properties with 'Format' constraint has to be 'string'
         *   parameter $pattern must not be null
         */
        if( $dataValue->getType() != 'string' || $pattern == null ) {
            return new CheckResult( $propertyId, $dataValue, 'Format', $parameters, 'error' );
        }

        $stringToCompare = $dataValue->getValue();

        $status = preg_match( '/^' . str_replace( '/', '\/', $pattern ) . '$/', $stringToCompare  ) ? 'compliance' : 'violation';

        return new CheckResult( $propertyId, $dataValue, 'Format', $parameters, $status );
    }

}