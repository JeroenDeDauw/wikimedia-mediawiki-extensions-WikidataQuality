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
        // Get globe coordinate parser and formatter
        $globeParser = new GlobeCoordinateParser();
        $globeFormatter = new GlobeCoordinateFormatter();

        // Set local values
        $formattedDataValue = $globeFormatter->format( $this->dataValue );
        $this->localValues = array( $formattedDataValue );

        //Compare each external value
        if ( $this->externalValues ) {
            foreach ( $this->externalValues as $externalValue ) {
                $parsedExternalValue = $globeParser->parse( $externalValue );
                if ( $formattedDataValue == $globeFormatter->format( $parsedExternalValue ) ) {
                    return true;
                }
            }
        }

        return false;
    }
}