<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;


use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\OutputLimiter;
use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class OneOfChecker {

    public function checkOneOfConstraint( $propertyId, $dataValueString, $values ) {
        $allowedValues = $this->convertStringFromTemplatesToArray( $values );

        if( !in_array($dataValueString, $allowedValues) ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        $parameterString = 'Values: ' . OutputLimiter::limitOutput( $values );

        return new CheckResult( $propertyId, $dataValueString, 'One of', $parameterString, $status );
    }
}