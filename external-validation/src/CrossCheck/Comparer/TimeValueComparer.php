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
        // Get time parser and formatter
        $parserOptions = new ParserOptions();
        $parserOptions->setOption( ValueParser::OPT_LANG, $this->dumpMetaInformation->getLanguage() );
        $timeParser = new TimeParser( $parserOptions );

        $formatterOptions = new FormatterOptions();
        $formatterOptions->setOption( ValueFormatter::OPT_LANG, $this->dumpMetaInformation->getLanguage() );
        $timeFormatter = new MwTimeIsoFormatter( $formatterOptions );

        // Set local values
        $formattedDataValue = $timeFormatter->format( $this->dataValue->getValue() );
        $this->localValues = array( $formattedDataValue );

        // Compare each external value
        if ( $this->externalValues ) {
            foreach ( $this->externalValues as $externalValue ) {
                $parsedExternalValue = $timeParser->parse( $externalValue );
                if ( $formattedDataValue == $timeFormatter->format( $parsedExternalValue ) ) {
                    return true;
                }
            }
        }

        return false;
    }
}