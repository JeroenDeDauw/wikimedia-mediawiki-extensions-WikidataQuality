<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use ValueFormatters\FormatterOptions;
use DataValues\Geo\Formatters\GlobeCoordinateFormatter;
use ValueParsers\ParserOptions;
use DataValues\Geo\Parsers\GlobeCoordinateParser;
use DataValues\DataValue;
use DataValues\Geo\Formatters\GeoCoordinateFormatter;


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
        // Set opts and get parser
        $opts = new ParserOptions();
        $parser = new GlobeCoordinateParser( $opts );

        // Compare values
        $result = false;

        if ( $parser ) {
            $localGlobeCoordinateValue = $this->dataValue;
            $externalGlobeCoordinateValue = $parser->parse( $this->externalValues[ 0 ] );

            if ( $externalGlobeCoordinateValue instanceof DataValue && $localGlobeCoordinateValue instanceof DataValue ) {
                // format
                $optsFormatter = new FormatterOptions();
                $optsFormatter->setOption( GeoCoordinateFormatter::OPT_FORMAT, GeoCoordinateFormatter::TYPE_DMS);
                $optsFormatter->setOption( GeoCoordinateFormatter::OPT_DIRECTIONAL, 'directional');

                $formatter = new GlobeCoordinateFormatter ( $optsFormatter );
                $localGlobeCoordinateValue = $formatter->format( $localGlobeCoordinateValue );
                $externalGlobeCoordinateValue = $formatter->format( $externalGlobeCoordinateValue );

                //compare
                $result = $localGlobeCoordinateValue === $externalGlobeCoordinateValue;
                $this->localValues = array( $localGlobeCoordinateValue );
                $this->externalValues = array( $externalGlobeCoordinateValue );
            }
        }

        return $result;
    }
}