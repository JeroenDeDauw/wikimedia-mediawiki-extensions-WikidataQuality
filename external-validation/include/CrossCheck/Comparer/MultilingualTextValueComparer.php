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
     * @var MonolingualTextValueComparer
     */
    private $monolingualTextValueComparer;


    /**
     * @param DumpMetaInformation $dumpMetaInformation
     * @param MultilingualTextValue $localValue
     * @param array $externalValues
     */
    public function __construct( $dumpMetaInformation, MultilingualTextValue $localValue, $externalValues )
    {
        parent::__construct( $dumpMetaInformation, $localValue, $externalValues );

        // Check, if multilingual text value contains text in language of dump
        foreach ( $localValue->getTexts() as $textValue ) {
            if ( $textValue->getLanguageCode() == $dumpMetaInformation->getLanguage() ) {
                $this->monolingualTextValueComparer = new MonolingualTextValueComparer( $dumpMetaInformation, $textValue, $externalValues );
                return;
            }
        }
    }


    /**
     * Starts the comparison of given DataValue and values of external database.
     * @return bool - result of the comparison.
     */
    public function execute()
    {
        // Parse external values
        $this->parseExternalValues();

        // Compare external values
        $result = false;
        if ( $this->monolingualTextValueComparer ) {
            $result = $this->monolingualTextValueComparer->execute();
        }

        return $result;
    }
}