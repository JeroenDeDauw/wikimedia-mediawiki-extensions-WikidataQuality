<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Class RangeChecker.
 * Checks 'Range' and 'Diff within range' constraints.
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Checker
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class RangeChecker {

    /**
     * List of all statements of given entity.
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
     * @param ConstraintReportHelper$helper
     */
    public function __construct( $statements, $helper )
    {
        $this->statements = $statements;
        $this->helper = $helper;
    }

    /**
     * Checks 'Range' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param string $minimum_quantity
     * @param string $maximum_quantity
     * @param string $minimum_date
     * @param string $maximum_date
     * @return CheckResult
     */
    public function checkRangeConstraint( $propertyId, $dataValue, $minimum_quantity, $maximum_quantity, $minimum_date, $maximum_date ) {
        $parameters = array();

        /*
         * error handling:
         *   type of $dataValue for properties with 'Range' constraint has to be 'quantity' or 'time' (also 'number' and 'decimal' could work)
         *   parameters ($minimum_quantity and $maximum_quantity) or ($minimum_date and $maximum_date) must not be null
         */
        if( $dataValue->getType() === 'quantity' && ( $minimum_quantity !== null && $maximum_quantity !== null && $minimum_date === null && $maximum_date === null ) ) {
            $min = $minimum_quantity;
            $max = $maximum_quantity;
            $parameters['minimum_quantity'] = array( $minimum_quantity );
            $parameters['maximum_quantity'] = array( $maximum_quantity );
        } else if( $dataValue->getType() === 'time' && ( $minimum_quantity === null && $maximum_quantity === null && $minimum_date !== null && $maximum_date !== null ) ) {
            $min = $minimum_date->getTime();
            $max = $maximum_date->getTime();
            $parameters['minimum_date'] = array( $minimum_date );
            $parameters['maximum_date'] = array( $maximum_date );
        } else {
            return new CheckResult( $propertyId, $dataValue, 'Range', array(), 'error' );
        }

        $comparativeValue = $this->getComparativeValue( $dataValue );

        if( $comparativeValue < $min || $comparativeValue > $max ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        return new CheckResult( $propertyId, $dataValue, 'Range', $parameters, $status );
    }

    /**
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param string $property
     * @param string $minimum_quantity
     * @param string $maximum_quantity
     * @return CheckResult
     */
    public function checkDiffWithinRangeConstraint( $propertyId, $dataValue, $property, $minimum_quantity, $maximum_quantity ) {
        $parameters = array();

        if( $property == null ) {
            $parameters['property'] = array( 'null' );
        } else {
            $parameters['property'] = array( new PropertyId( $property ) );
        }

        /*
         * error handling:
         *   type of $dataValue for properties with 'Diff within range' constraint has to be 'quantity' or 'time' (also 'number' and 'decimal' could work)
         *   parameters $property, $minimum_quantity and $maximum_quantity must not be null
         */
        if( ( $dataValue->getType() === 'quantity' || $dataValue->getType() === 'time' ) && ( $property !== null && $minimum_quantity !== null && $maximum_quantity !== null ) ) {
            $min = $minimum_quantity;
            $max = $maximum_quantity;
            $parameters['minimum_quantity'] = array( $minimum_quantity );
            $parameters['maximum_quantity'] = array( $maximum_quantity );
        } else {
            return new CheckResult( $propertyId, $dataValue, 'Diff within range', array(), 'error' );
        }

        $thisValue = $this->getComparativeValue( $dataValue );

        // checks only the first occurence of the referenced property (this constraint implies a single value constraint on that property)
        foreach( $this->statements as $statement ) {
            if( $property == $statement->getClaim()->getPropertyId()->getSerialization() ) {
                $mainSnak = $statement->getClaim()->getMainSnak();

                /*
                 * error handling:
                 *   types of this and the other value have to be equal, both must contain actual values
                 */
                if( $mainSnak->getDataValue()->getType() === $dataValue->getType() && $mainSnak->getType() === 'value' ) {

                    $thatValue = $this->getComparativeValue( $mainSnak->getDataValue() );

                    // negative difference is possible
                    $diff = $thisValue-$thatValue;

                    if( $diff < $min || $diff > $max ) {
                        $status = 'violation';
                    } else {
                        $status = 'compliance';
                    }
                } else {
                    $status = 'violation';
                }

                return new CheckResult( $propertyId, $dataValue, 'Diff within range', $parameters, $status );
            }
        }
    }

    private function getComparativeValue( $dataValue ) {
        if( $dataValue->getType() === 'time' ) {
            return $dataValue->getTime();
        } else {
            // 'quantity'
            return $dataValue->getAmount()->getValue();
        }
    }

}