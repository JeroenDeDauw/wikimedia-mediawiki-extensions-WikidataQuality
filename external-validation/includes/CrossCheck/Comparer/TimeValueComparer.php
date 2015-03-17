<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueParsers\ParserOptions;
use ValueParsers\ValueParser;
use Wikibase\Lib\MwTimeIsoFormatter;
use Wikibase\Lib\Parsers\TimeParser;


/**
 * Class TimeValueComparer
 * @package WikidataQuality\ExternalValidation\CrossCheck\Comparer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class TimeValueComparer extends DataValueComparer
{
    /**
     * Array of DataValue classes that are supported by the current comparer.
     * @var array
     */
    public static $acceptedDataValues = array( 'DataValues\TimeValue' );


    /**
     * Starts the comparison of given TimeValue and values of external database.
     * @return bool - result of the comparison.
     */
    public function execute()
    {
        // Get formatter
        $formatterOptions = new FormatterOptions();
        $formatterOptions->setOption( ValueFormatter::OPT_LANG, $this->dumpMetaInformation->getLanguage() );
        $timeFormatter = new MwTimeIsoFormatter( $formatterOptions );

        // Format local value
        $formattedDataValue = $timeFormatter->format( $this->localValue );

        // Parse external values
        $this->parseExternalValues();

        // Compare each external value with local value
        if ( $this->externalValues ) {
            foreach ( $this->externalValues as $externalValue ) {
                $formattedExternalValue = $timeFormatter->format( $externalValue );
                if ( $formattedDataValue == $formattedExternalValue ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns parser that is used to parse strings of external values to Wikibase DataValues.
     * @return TimeParser
     */
    protected function getExternalValueParser()
    {
        $parserOptions = new ParserOptions();
        $parserOptions->setOption( ValueParser::OPT_LANG, $this->dumpMetaInformation->getLanguage() );

        return new TimeParser( $parserOptions );
    }
}