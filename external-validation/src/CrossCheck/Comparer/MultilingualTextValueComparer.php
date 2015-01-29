<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use DataValues\MultilingualTextValue;


/**
 * Class MultilingualTextValueComparer
 * @package WikidataQuality\ExternalValidation\CrossCheck\Comparer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class MultilingualTextValueComparer extends MonolingualTextValueComparer
{
    /**
     * Array of DataValue classes that are supported by the current comparer.
     * @var array
     */
    public static $acceptedDataValues = array( 'DataValues\MultilingualTextValue' );


    /**
     * @param MultilingualTextValue $dataValue
     * @param array $externalValues
     * @param array $localValues
     */
    public function __construct( MultilingualTextValue $dataValue, $externalValues, $localValues = null )
    {
        foreach ( $dataValue->getTexts() as $text ) {
            if ( $text->getLanguageCode() == "de" ) { //TODO: Get from database
                parent::__construct( $text, $externalValues );
            }
        }
    }
}