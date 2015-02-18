<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Helper;


class ConstraintReportHelper {

    private $showMax = 50;

    public function dataValueToString( $dataValue )
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

    public function limitOutput( $string ) {
        if( strlen($string) <= $this->showMax ) {
            return $string;
        } else {
            return substr( $string, 0, $this->showMax ) . '...';
        }
    }

    public function toArray( $templateString ) {
        $toReplace = array("{", "}", "|", "[", "]", " ");
        return explode(",", str_replace($toReplace, "", $templateString));
    }

    public function toStringWithoutBrackets( $templateString ) {
        $toReplace = array("{", "}", "|", "[", "]");
        return str_replace( $toReplace, '', $templateString);
    }
}