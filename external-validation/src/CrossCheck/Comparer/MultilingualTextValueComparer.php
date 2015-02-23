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
    public function __construct( $dumpMetaInformation, MultilingualTextValue $dataValue, $externalValues )
    {
        foreach ( $dataValue->getTexts() as $text ) {
            if ( $text->getLanguageCode() == $dumpMetaInformation->getLanguage() ) {
                parent::__construct( $dumpMetaInformation, $text, $externalValues );
                return;
            }
        }

        // If multilingual text does not contain text in language of dump, initialize variables manually
        $this->dumpMetaInformation = $dumpMetaInformation;
        $this->externalValues = $externalValues;
    }


    /**
     * Starts the comparison of given DataValue and values of external database.
     * @return bool - result of the comparison.
     */
    public function execute()
    {
        if ( $this->dataValue ) {
            // Multilingual text contains text in language of dump
            return parent::execute();
        } else {
            // Multilingual text does not contain text in language of dump
            $this->localValues = array();
            return false;
        }
    }
}