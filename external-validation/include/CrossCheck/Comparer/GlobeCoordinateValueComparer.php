<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use DataValues\Geo\Formatters\GlobeCoordinateFormatter;
use DataValues\Geo\Parsers\GlobeCoordinateParser;


/**
 * Class GlobeCoordinateComparer
 * @package WikidataQuality\ExternalValidation\CrossCheck\Comparer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class GlobeCoordinateValueComparer extends DataValueComparer
{
    /**
     * Array of DataValue classes that are supported by the current comparer.
     * @var array
     */
    public static $acceptedDataValues = array( 'DataValues\Geo\Values\GlobeCoordinateValue' );


    /**
     * Starts the comparison of given GlobeCoordinateValue and values of external database.
     * @return bool - result of the comparison.
     */
    public function execute()
    {
        // Format local value
        $globeFormatter = new GlobeCoordinateFormatter();
        $formattedDataValue = $globeFormatter->format( $this->localValue );

        // Parse external values
        $this->parseExternalValues();

        // Compare each external value with local value
        if ( $this->externalValues ) {
            foreach ( $this->externalValues as $externalValue ) {
                $formattedExternalValue = $globeFormatter->format( $externalValue );
                if ( $formattedDataValue == $formattedExternalValue ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns parser that is used to parse strings of external values to Wikibase DataValues.
     * @return GlobeCoordinateParser
     */
    protected function getExternalValueParser()
    {
        return new GlobeCoordinateParser();
    }
}