<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Helper;


class ConstraintReportHelper {

    private $showMax = 50;

    /**
     * @param $claim
     * @return string
     */
    public function getDataValueString( $claim )
    {
        $mainSnak = $claim->getMainSnak();
        if( $mainSnak->getType() == 'value' ) {
            return $this->dataValueToString( $mainSnak->getDataValue() );
        } else {
            return '\'\'(' . $mainSnak->getType() . '\'\')';
        }
    }

    /**
     * @param $dataValue
     * @return mixed|string
     */
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
                return array_key_exists('en', $dataValue) ? $dataValue->getTexts()['en'] : array_shift( $dataValue->getTexts() );;
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

    public function removeBrackets( $templateString ) {
        $toReplace = array("{", "}", "|", "[", "]");
        return str_replace( $toReplace, "", $templateString);
    }

    public function stringToArray( $templateString ) {
        return explode(",", $this->removeBrackets( str_replace(" ", "", $templateString ) ) );
    }

    public function arrayToString( $templateArray ) {
        return implode(", ", $templateArray);
    }
}