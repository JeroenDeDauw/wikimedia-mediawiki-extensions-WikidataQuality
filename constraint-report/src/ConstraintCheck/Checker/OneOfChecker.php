<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;


use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\OutputLimiter;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\TemplateConverter;
use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class OneOfChecker {

    private $helper;

    public function __construct( $helper ) {
        $this->helper = $helper;
    }

    public function checkOneOfConstraint( $propertyId, $dataValueString, $values ) {
        $allowedValues = $this->helper->toArray( $values );

        if( !in_array($dataValueString, $allowedValues) ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        $parameterString = 'values: ' . $this->helper->limitOutput( $this->helper->toStringWithoutBrackets( $values ) );

        return new CheckResult( $propertyId, $dataValueString, 'One of', $parameterString, $status );
    }
}