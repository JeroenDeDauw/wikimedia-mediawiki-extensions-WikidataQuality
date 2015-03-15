<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\ItemId;

class OneOfChecker {

    private $helper;

    public function __construct( $helper ) {
        $this->helper = $helper;
    }

    public function checkOneOfConstraint( $propertyId, $dataValue, $itemArray ) {
        $parameters = array();

        if( empty( $itemArray ) ) {
            $parameters['item'] = array( 'null' );
        } else {
            $func = function( $item ) {
                return new ItemId( $item );
            };
            $parameters['item'] = array_map( $func, $itemArray );
        }

        /*
         * error handling:
         *   type of $dataValue for properties with 'One of' constraint has to be 'wikidata-entityid'
         *   parameter $itemArray must not be null
         */
        if( $dataValue->getType() != 'wikibase-entityid' || $itemArray == null) {
            return new CheckResult( $propertyId, $dataValue, 'Format', $parameters, 'error' );
        }

        if( !in_array( $dataValue, $itemArray ) ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        return new CheckResult( $propertyId, $dataValue, 'One of', $parameters, $status );
    }

}