<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class FormatChecker {

    public function checkFormatConstraint( $propertyId, $dataValueString, $pattern ) {
        $status = preg_match( '/' . $pattern . '/', $dataValueString) ? 'compliance' : 'violation';
        return new CheckResult( $propertyId, $dataValueString, 'Format', '\'\'(none)\'\'', $status);
    }
}