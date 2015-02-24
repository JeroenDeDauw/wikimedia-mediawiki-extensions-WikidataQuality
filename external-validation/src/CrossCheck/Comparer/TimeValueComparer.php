<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use Wikibase\Lib\MwTimeIsoFormatter;
use ValueParsers\TimeParser;
use ValueParsers\ParserOptions;
use ValueParsers\ValueParser;
use Wikibase\Lib\Parsers\DateTimeParser;
use Wikibase\Lib\Parsers\EraParser;
use DataValues\DataValue;


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
        // Set opts and get parser
        $opts = new ParserOptions();
        $opts->setOption( TimeParser::OPT_CALENDAR, TimeParser::CALENDAR_GREGORIAN );
        $opts->setOption( ValueParser::OPT_LANG, $this->dumpMetaInformation->getLanguage() );

        $parser = null;
        if ($this->dumpMetaInformation->getDateFormat() === 'd.m.Y'){   # Look at TimeParserTest.php to determine which parser is needed
            $parser = new DateTimeParser( new EraParser(), $opts );
        }

        // Compare values
        $result = false;

        if ( $parser ) {
            $externalTimeValue = $parser->parse( $this->externalValues[ 0 ] );
            $localTimeValue = $this->dataValue;

            if ( $externalTimeValue instanceof DataValue && $localTimeValue instanceof DataValue ) {
                // format
                $optsFormatter = new FormatterOptions();
                $optsFormatter->setOption( ValueFormatter::OPT_LANG, $this->dumpMetaInformation->getLanguage() );

                $formatter = new MwTimeIsoFormatter( $optsFormatter );
                $localTimeValue = $formatter->format( $localTimeValue );
                $externalTimeValue = $formatter->format( $externalTimeValue );

                //compare
                $result = $localTimeValue === $externalTimeValue;
                $this->localValues = array( $localTimeValue );
                $this->externalValues = array( $externalTimeValue );
            }
        }

        return $result;
    }
}