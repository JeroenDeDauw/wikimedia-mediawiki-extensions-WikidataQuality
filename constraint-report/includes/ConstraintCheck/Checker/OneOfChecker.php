<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\ItemId;

/**
 * Class OneOfChecker.
 * Checks 'One of' constraint.
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
     * Checks 'One of' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param array $itemArray
     * @return CheckResult
     */
    public function checkOneOfConstraint( $propertyId, $dataValue, $itemArray ) {
        $parameters = array();

        $parameters['item'] = $this->helper->parseParameterArray( $itemArray, 'ItemId' );

        /*
         * error handling:
         *   type of $dataValue for properties with 'One of' constraint has to be 'wikibase-entityid'
         *   parameter $itemArray must not be null
         */
        if( $dataValue->getType() !== 'wikibase-entityid' ) {
            $message = 'Properties with \'One of\' constraint need to have values of type \'wikibase-entityid\'.';
            return new CheckResult( $propertyId, $dataValue, 'Format', $parameters, 'violation', $message );
        }
        if( $itemArray[0] === '' ) {
            $message = 'Properties with \'One of\' constraint need a parameter \'item\'.';
            return new CheckResult( $propertyId, $dataValue, 'One of', $parameters, 'violation', $message );
        }

        if( in_array( $dataValue->getEntityId()->getSerialization(), $itemArray ) ) {
            $message = '';
            $status = 'compliance';
        } else {
            $message = 'The property\'s value must be one of the items defined in the parameters.';
            $status = 'violation';
        }

        return new CheckResult( $propertyId, $dataValue, 'One of', $parameters, $status, $message );
    }

}