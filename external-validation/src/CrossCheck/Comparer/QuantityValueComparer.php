<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;

/**
 * Class QuantityValueComparer
 * @package WikidataQuality\ExternalValidation\CrossCheck\Comparer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class QuantityValueComparer extends DataValueComparer {
    /**
     * Array of DataValue classes that are supported by the current comparer.
     * @var array
     */
    public static $acceptedDataValues = array( 'DataValues\QuantityValue' );


    /**
     * Starts the comparison of given QuantityValue and values of external database.
     * @return bool - result of the comparison.
     */
    public function execute()
    {
        // Get local bounds
        $lowerBound = $this->dataValue->getLowerBound()->getValueFloat();
        $upperBound = $this->dataValue->getUpperBound()->getValueFloat();

        // Set local values
        $ammount = $this->dataValue->getAmount()->getValueFloat();
        $uncertainty = $this->dataValue->getUncertainty() / 2;
        $this->localValues = array( "$ammount ±$uncertainty" );

        foreach ( $this->externalValues as $externalValue ) {
            // Convert given string to float
            $externalValue = (float)( $externalValue );

            // Compare
            if( $externalValue >= $lowerBound && $externalValue <= $upperBound ) {
                return true;
            }
        }

        return false;
    }
}