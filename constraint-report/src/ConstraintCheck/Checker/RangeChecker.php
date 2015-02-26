<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\DataValueParser;
use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class RangeChecker {

    private $statements;
    private $helper;

    public function __construct($statements, $helper)
    {
        $this->statements = $statements;
        $this->helper = $helper;
    }

    public function checkRangeConstraint( $propertyId, $dataValueString, $minimum_quantity, $maximum_quantity, $minimum_date, $maximum_date ) {
        if( $minimum_quantity != null && $maximum_quantity != null && $minimum_date == null && $maximum_date == null ) {
            $min = $minimum_quantity;
            $max = $maximum_quantity;
        } else if( $minimum_quantity == null && $maximum_quantity == null && $minimum_date != null && $maximum_date != null ) {
            $min = $minimum_date;
            $max = $maximum_date;
        } else {
            return new CheckResult( $propertyId, $dataValueString, "Range", '\'\'(erroneous min/max)\'\'', 'error' );
        }

        if( $dataValueString < $min || $dataValueString > $max ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        $parameterString = $this->helper->limitOutput( 'min: ' . $min . ', max: ' . $max );

        return new CheckResult($propertyId, $dataValueString, "Range", $parameterString, $status );
    }


    public function checkDiffWithinRangeConstraint( $propertyId, $dataValueString, $property, $minimum_quantity, $maximum_quantity, $minimum_date, $maximum_date ) {
        if( $minimum_quantity != null && $maximum_quantity != null && $minimum_date == null && $maximum_date == null ) {
            $min = $minimum_quantity;
            $max = $maximum_quantity;
        } else if( $minimum_quantity == null && $maximum_quantity == null && $minimum_date != null && $maximum_date != null ) {
            $min = $minimum_date;
            $max = $maximum_date;
        } else {
            return new CheckResult( $propertyId, $dataValueString, "Diff within range", 'property: ' . $property . ', \'\'(erroneous min/max)\'\'', 'error' );
        }

        $parameterString = $this->helper->limitOutput( 'property: ' . $property . ', min: ' . $min . ', max: ' . $max );

        foreach( $this->statements as $statement ) {
            if( $property == $statement->getClaim()->getPropertyId() ) {
                $mainSnak = $statement->getClaim()->getMainSnak();

                if( $mainSnak->getType() == 'value' ) {
                    $basePropertyDataValueString = $this->helper->dataValueToString( $mainSnak->getDataValue() );

                    $diff = abs( $dataValueString-$basePropertyDataValueString );

                    if( $diff < $min || $diff > $max ) {
                        $status = 'violation';
                    } else {
                        $status = 'compliance';
                    }
                } else {
                    $status = 'violation';
                }

                return new CheckResult( $propertyId, $dataValueString, "Diff within range", $parameterString, $status );
            }
        }
    }

}