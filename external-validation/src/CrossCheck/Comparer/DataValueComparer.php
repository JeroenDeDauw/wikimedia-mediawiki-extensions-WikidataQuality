<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


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
        'WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer'
    );

    /**
     * Wikibase data value.
     * @var DataValue
     */
    protected $dataValue;

    /**
     * Local, probably converted values.
     * @var array
     */
    public $localValues;

    /**
     * External database values.
     * @var array
     */
    public $externalValues;


    /**
     * @param \DataValue $dataValue - wikibase DataValue
     * @param array $externalValues - external database values
     */
    public function __construct( DataValue $dataValue, $externalValues, $localValues = null )
    {
        $this->dataValue = $dataValue;
        $this->externalValues = $externalValues;
        $this->localValues = $localValues;
    }


    /**
     * Starts the comparison of given DataValue and values of external database.
     * @return bool - result of the comparison.
     */
    public abstract function execute();


    /**
     * Returns an instance of a comparer suitable to the given DataValue.
     * @param \DataValue $dataValue - wikibase DataValue
     * @param array $externalValues - external database values
     * @return DataValueComparer
     */
    public static function getComparer( DataValue $dataValue, $externalValues )
    {
        foreach ( self::$comparers as $comparer ) {
            $reflector = new ReflectionClass( $comparer );
            $acceptedDataValues = $reflector->getStaticPropertyValue( "acceptedDataValues" );
            $dataValueClass = get_class( $dataValue );
            if ( in_array( $dataValueClass, $acceptedDataValues ) ) {
                return new $comparer( $dataValue, $externalValues );
            }
        }
    }
}