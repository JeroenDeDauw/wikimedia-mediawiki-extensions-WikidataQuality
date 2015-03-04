<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class OneOfChecker {

    private $helper;

    public function __construct( $helper ) {
        $this->helper = $helper;
    }

    public function checkOneOfConstraint( $propertyId, $dataValueString, $itemArray ) {

        if( !in_array( $dataValueString, $itemArray ) ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        $parameterString = 'values: ' . $this->helper->arrayToString( $itemArray );

        return new CheckResult( $propertyId, $dataValueString, 'One of', $parameterString, $status );
    }
}