<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use Doctrine\Instantiator\Exception\InvalidArgumentException;
use ReflectionClass;
use DataValues\DataValue;


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
        'WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer'
    );

    /**
     * Meta information of the current dump.
     * @var DumpMetaInformation
     */
    protected $dumpMetaInformation;

    /**
     * Wikibase data value.
     * @var DataValue
     */
    protected $dataValue;

    /**
     * Local, probably converted values.
     * @var array
     */
    protected $localValues;

    /**
     * External database values.
     * @var array
     */
    protected $externalValues;


    /**
     * @param $dumpMetaInformation
     * @param DataValue $dataValue - wikibase DataValue
     * @param array $externalValues - external database values
     * @param $localValues
     */
    public function __construct( $dumpMetaInformation, DataValue $dataValue, $externalValues )
    {
        // Check types of parameters
        if( $externalValues && !is_array( $externalValues ) ) {
            throw new InvalidArgumentException( '$externalValues must be null or array.' );
        }

        // Set parameters
        $this->dumpMetaInformation = $dumpMetaInformation;
        $this->dataValue = $dataValue;
        $this->externalValues = $externalValues;
    }


    /**
     * Starts the comparison of given DataValue and values of external database.
     * @return bool - result of the comparison.
     */
    public abstract function execute();


    /**
     * Meta information of the current dump.
     * @return DumpMetaInformation
     */
    public function getDumpMetaInformation() {
        return $this->dumpMetaInformation;
    }

    /**
     * Wikibase data value.
     * @return DataValue
     */
    public function getDataValue() {
        return $this->dataValue;
    }

    /**
     * Returns local, probably converted values.
     * @return array
     */
    public function getLocalValues() {
        return $this->localValues;
    }

    /**
     * Returns external database values.
     * @return array
     */
    public function getExternalValues() {
        return $this->externalValues;
    }


    /**
     * Returns an instance of a comparer suitable to the given DataValue.
     * @param array $dumpMetaInformation
     * @param \DataValue $dataValue - wikibase DataValue
     * @param array $externalValues - external database values
     * @return DataValueComparer or null
     */
    public static function getComparer( $dumpMetaInformation, DataValue $dataValue, $externalValues )
    {
        foreach ( self::$comparers as $comparer ) {
            $reflector = new ReflectionClass( $comparer );
            $acceptedDataValues = $reflector->getStaticPropertyValue( 'acceptedDataValues' );
            $dataValueClass = get_class( $dataValue );
            if ( in_array( $dataValueClass, $acceptedDataValues ) ) {
                return new $comparer( $dumpMetaInformation, $dataValue, $externalValues );
            }
        }

        return null;
    }
}