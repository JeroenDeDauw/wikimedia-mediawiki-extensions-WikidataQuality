<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use ValueParsers\ParserOptions;
use ValueParsers\QuantityParser;
use ValueParsers\ValueParser;


/**
 * Class QuantityValueComparer
 * @package WikidataQuality\ExternalValidation\CrossCheck\Comparer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class QuantityValueComparer extends DataValueComparer
{
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
        // Parse external values
        $this->parseExternalValues();

        // Compare each external value with local value
        if ( $this->externalValues ) {
            foreach ( $this->externalValues as $externalValue ) {
                if ( $externalValue->getLowerBound() <= $this->localValue->getUpperBound() &&
                    $externalValue->getUpperBound() >= $this->localValue->getLowerBound()
                ) {
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
        $parserOptions = new ParserOptions();
        $parserOptions->setOption( ValueParser::OPT_LANG, $this->dumpMetaInformation->getLanguage() );

        return new QuantityParser( $parserOptions );
    }
}