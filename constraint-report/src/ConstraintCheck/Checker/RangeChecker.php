<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class RangeChecker {

    private $statements;

    public function __construct($statements)
    {
        $this->statements = $statements;
    }

    public function checkRangeConstraint( $propertyId, $dataValueString, $min, $max ) {
        if( $dataValueString < $min || $dataValueString > $max ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        $parameterString = 'min: ' . $min . ', max: ' . $max;

        return new CheckResult($propertyId, $dataValueString, "Range", $parameterString, $status );
    }


    public function checkDiffWithinRangeConstraint( $propertyId, $dataValueString, $basePropertyId, $min, $max, $entityStatements ) {
        $parameterString = 'base Property: ' . $basePropertyId . ', min: ' . $min . ', max: ' . $max;

        foreach( $entityStatements as $statement ) {
            if( $basePropertyId == $statement->getClaim()->getPropertyId() ) {
                $mainSnak = $statement->getClaim()->getMainSnak();

                if( $mainSnak->getType() == 'value' ) {
                    $basePropertyDataValueString = $this->dataValueToString( $mainSnak->getDataValue() );

                    $diff = abs( $dataValueString-$basePropertyDataValueString );

                    if( $diff < $min || $diff > $max ) {
                        $status = 'violation';
                    } else {
                        $status = 'compliance';
                    }
                } else {
                    $status = 'violation';
                }

                return new CheckResult($propertyId, $dataValueString, "Diff within range", $parameterString, $status );
            }
        }
    }

}