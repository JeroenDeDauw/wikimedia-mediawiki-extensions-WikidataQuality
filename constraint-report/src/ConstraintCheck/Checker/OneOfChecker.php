<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\ItemId;

/**
 * Class OneOfChecker
 * Checks One of constraint.
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Checker
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class OneOfChecker {

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
     * Checks One of constraint
     * @param PropertyId $propertyId
     * @param Data $dataValue
     * @param array $itemArray
     * @return CheckResult
     */
    public function checkOneOfConstraint( $propertyId, $dataValue, $itemArray ) {
        $parameters = array();

        if( empty( $itemArray ) ) {
            $parameters['item'] = array( 'null' );
        } else {
            $func = function( $item ) {
                if( $item !== 'novalue' && $item ==! 'somevalue' ) {
                    return new ItemId( $item );
                } else {
                    return $item;
                }

            };
            $parameters['item'] = array_map( $func, $itemArray );
        }

        /*
         * error handling:
         *   type of $dataValue for properties with 'One of' constraint has to be 'wikibase-entityid'
         *   parameter $itemArray must not be null
         */
        if( $dataValue->getType() !== 'wikibase-entityid' || $itemArray === null) {
            return new CheckResult( $propertyId, $dataValue, 'Format', $parameters, 'error' );
        }

        if( !in_array( new ItemId( $dataValue->getEntityId()->getSerialization() ), $itemArray ) ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        return new CheckResult( $propertyId, $dataValue, 'One of', $parameters, $status );
    }

}