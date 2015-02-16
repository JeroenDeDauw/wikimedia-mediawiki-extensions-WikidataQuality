<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class FormatChecker {

    public function checkFormatConstraint( $propertyId, $dataValueString ) {
        return new CheckResult( $propertyId, $dataValueString, 'Format', '', 'todo');
    }
}