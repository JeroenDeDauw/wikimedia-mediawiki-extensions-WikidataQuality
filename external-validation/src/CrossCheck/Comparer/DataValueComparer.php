<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use DataValues\DataValue;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use ReflectionClass;
use ValueParsers\ParserOptions;
use ValueParsers\ValueParser;
use Wikibase\Parsers\MonolingualTextParser;


/**
 * Class AbstractComparer
 * @package WikidataQuality\ExternalValidation\CrossCheck\Comparer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
abstract class DataValueComparer
{
    /**
     * Array of registered comparers
     * @var array
     */
    private static $comparers = array(
        'WikidataQuality\ExternalValidation\CrossCheck\Comparer\EntityIdValueComparer',
        'WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer',
        'WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer',
        'WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer',
        'WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer',
        'WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer',
        'WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer'
    );

    /**
     * Meta information of the current dump.
     * @var DumpMetaInformation
     */
    protected $dumpMetaInformation;

    /**
     * Wikibase data value for comparison.
     * @var array
     */
    protected $localValue;

    /**
     * Data values from external database for comparison.
     * @var array
     */
    protected $externalValues;


    /**
     * @param $dumpMetaInformation
     * @param DataValue $localValue - Wikibase data value
     * @param array $externalValues - external database data values
     */
    public function __construct( $dumpMetaInformation, DataValue $localValue, $externalValues )
    {
        // Check types of parameters
        if ( $externalValues && !is_array( $externalValues ) ) {
            throw new InvalidArgumentException( '$externalValues must be null or array.' );
        }

        // Set parameters
        $this->dumpMetaInformation = $dumpMetaInformation;
        $this->localValue = $localValue;
        $this->externalValues = $externalValues;
    }


    /**
     * Starts the comparison of given DataValue and values of external database.
     * @return bool - result of the comparison.
     */
    public abstract function execute();

    /**
     * Returns parser that is used to parse strings of external values to Wikibase DataValues.
     * @return ValueParser
     */
    protected function getExternalValueParser()
    {
        $options = new ParserOptions();
        $options->setOption( 'valuelang', $this->dumpMetaInformation->getLanguage() );
        return new MonolingualTextParser( $options );
    }


    /**
     * Parses each string in externalValues array to Wikibase DataValue.
     */
    protected function parseExternalValues()
    {
        if ( $this->externalValues ) {
            foreach ( $this->externalValues as $index => $externalValue ) {
                if ( is_string( $externalValue ) ) {
                    $parsedValue = $this->getExternalValueParser()->parse( $externalValue );
                    $this->externalValues[ $index ] = $parsedValue;
                }
            }

        }
    }


    /**
     * Meta information of the current dump.
     * @return DumpMetaInformation
     */
    public function getDumpMetaInformation()
    {
        return $this->dumpMetaInformation;
    }

    /**
     * Returns Wikibase data value.
     * @return array
     */
    public function getLocalValue()
    {
        return $this->localValue;
    }

    /**
     * Returns external database data values.
     * @return array
     */
    public function getExternalValues()
    {
        return $this->externalValues;
    }


    /**
     * Returns an instance of a comparer suitable to the given DataValue.
     * @param array $dumpMetaInformation
     * @param \DataValue $localValue - Wikibase data value
     * @param array $externalValues - external database data values
     * @return DataValueComparer|null
     */
    public static function getComparer( $dumpMetaInformation, DataValue $localValue, $externalValues )
    {
        foreach ( self::$comparers as $comparer ) {
            $reflector = new ReflectionClass( $comparer );
            $acceptedDataValues = $reflector->getStaticPropertyValue( 'acceptedDataValues' );
            $dataValueClass = get_class( $localValue );
            if ( in_array( $dataValueClass, $acceptedDataValues ) ) {
                return new $comparer( $dumpMetaInformation, $localValue, $externalValues );
            }
        }
    }
}