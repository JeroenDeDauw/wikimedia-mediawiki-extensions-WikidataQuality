<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Helper;

/**
 * Class ConstraintReportHelper
 * Class for helper functions for constraint checkers.
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Helper
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class ConstraintReportHelper {

    /**
     * @param string $templateString
     * @return string
     */
    public function removeBrackets( $templateString ) {
        $toReplace = array( "{", "}", "|", "[", "]" );
        return str_replace( $toReplace, "", $templateString );
    }

    /**
     * Used to convert a string containing a comma-separated list (as one gets out of the constraints table) to array
     * @param string $templateString
     * @return array
     */
    public function stringToArray( $templateString ) {
        return $templateString === "" ? array() : explode( ",", $this->removeBrackets( str_replace( " ", "", $templateString ) ) );
    }

}