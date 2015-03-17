<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


/**
 * Class MonolingualTextValueComparer
 * @package WikidataQuality\ExternalValidation\CrossCheck\Comparer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class MonolingualTextValueComparer extends DataValueComparer
{
    /**
     * Array of DataValue classes that are supported by the current comparer.
     * @var array
     */
    public static $acceptedDataValues = array( 'DataValues\MonolingualTextValue' );


    /**
     * Starts the comparison of given MonolingualTextValue and values of external database.
     * @return bool - result of the comparison.
     */
    public function execute()
    {
        // Compare value
        $result = false;
        if ( $this->externalValues && in_array( $this->localValue->getText(), $this->externalValues ) ) {
            $result = true;
        }

        // Parse external values
        $this->parseExternalValues();

        return $result;
    }
}