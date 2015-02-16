<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Helper;

class DataValueParser {

    public static function dataValueToString( $dataValue )
    {
        $dataValueType = $dataValue->getType();
        switch( $dataValueType ) {
            case 'string':
            case 'decimal':
            case 'number':
            case 'boolean':
            case 'unknown':
                return $dataValue->getValue();
            case 'quantity':
                return $dataValue->getAmount()->getValue();
            case 'time':
                return $dataValue->getTime();
            case 'globecoordinate':
            case 'geocoordinate':
                return 'Latitude: ' . $dataValue->getLatitude() . ', Longitude: ' . $dataValue->getLongitude();
            case 'monolingualtext':
                return $dataValue->getText();
            case 'multilingualtext':
                return array_key_exists('en', $dataValue) ? $dataValue->getTexts()['en'] : array_shift($dataValue->getTexts());;
            case 'wikibase-entityid':
                return $dataValue->getEntityId();
            case 'bad':
            default:
                return null;
            //error case
        }
    }
}