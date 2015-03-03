<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityFormatter;
use ValueFormatters\ValueFormatter;
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
        // Get time parser and formatter
        $parserOptions = new ParserOptions();
        $parserOptions->setOption( ValueParser::OPT_LANG, $this->dumpMetaInformation->getLanguage() );
        $timeParser = new QuantityParser( $parserOptions );

        $formatterOptions = new FormatterOptions();
        $formatterOptions->setOption( ValueFormatter::OPT_LANG, $this->dumpMetaInformation->getLanguage() );
        $timeFormatter = new QuantityFormatter( new DecimalFormatter( $formatterOptions ), $formatterOptions );

        // Set local values
        $formattedDataValue = $timeFormatter->format( $this->dataValue->getValue() );
        $this->localValues = array( $formattedDataValue );

        // Compare each external value
        if ( $this->externalValues ) {
            foreach ( $this->externalValues as $externalValue ) {
                $parsedExternalValue = $timeParser->parse( $externalValue );
                if ( $parsedExternalValue->getLowerBound() <= $this->dataValue->getUpperBound() &&
                    $parsedExternalValue->getUpperBound() >= $this->dataValue->getLowerBound()
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}