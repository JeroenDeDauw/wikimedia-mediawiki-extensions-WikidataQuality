<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Helper;

class ConstraintReportHelper {

    public function removeBrackets( $templateString ) {
        $toReplace = array( "{", "}", "|", "[", "]" );
        return str_replace( $toReplace, "", $templateString );
    }

    public function stringToArray( $templateString ) {
        return $templateString == "" ? array() : explode( ",", $this->removeBrackets( str_replace( " ", "", $templateString ) ) );
    }

    public function arrayToString( $templateArray ) {
        return implode( ", ", $templateArray );
    }

}