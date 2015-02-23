<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


/**
 * Class StringValueComparer
 * @package WikidataQuality\ExternalValidation\CrossCheck\Comparer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class StringValueComparer extends DataValueComparer
{
    /**
     * Array of DataValue classes that are supported by the current comparer.
     * @var array
     */
    public static $acceptedDataValues = array( 'DataValues\StringValue' );


    /**
     * Starts the comparison of given StringValue and values of external database.
     * @return bool - result of the comparison.
     */
    public function execute()
    {
        // Get monolingual text
        $this->localValues = array( $this->dataValue->getValue() );

        // Compare value
        if ( $this->localValues && $this->externalValues && count( array_intersect( $this->localValues, $this->externalValues ) ) > 0 ) {
            return true;
        } else {
            return false;
        }
    }
}