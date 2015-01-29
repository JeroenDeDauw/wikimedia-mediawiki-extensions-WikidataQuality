<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator;

use ReflectionClass;

/**
 * Class MappingEvaluator
 * @package WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator
 * @author BP2014N1
 * @license GNU GPL v2+
 */
abstract class MappingEvaluator {
    /**
     * Array of registered evaluators
     * @var array
     */
    private static $evaluators = array(
        'WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\XPathEvaluator'
    );

    /**
     * Contains the external data object
     * @var string
     */
    protected $externalData;


    /**
     * @param $externalData - external data object
     */
    public function __construct( $externalData ) {
        $this->externalData = $externalData;
    }


    /**
     * Evaluates a given query on external data object
     * @param string $nodeSelector - node selector query
     * @param string $valueFormatter - value formatter query
     * @return mixed - array of values
     */
    public abstract function evaluate( $nodeSelector, $valueFormatter = null );

    /**
     * Returns an instance of a mapping evaluator suitable to the given data format
     * @param string $dataFormat - data format for which a mapping evaluator should be created
     * @param string $externalData - external data object with that the evaluator should be initialized
     * @return MappingEvaluator
     */
    public static function getEvaluator( $dataFormat, $externalData ) {
        foreach( self::$evaluators as $evaluator ) {
            $reflector = new ReflectionClass( $evaluator );
            $acceptedDataFormats = $reflector->getStaticPropertyValue( "acceptedDataFormats" );
            if( in_array( $dataFormat, $acceptedDataFormats ) ) {
                return new $evaluator( $externalData );
            }
        }
    }
}