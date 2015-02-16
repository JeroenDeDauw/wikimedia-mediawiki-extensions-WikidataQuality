<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Helper;


class TemplateConverter {

    public static function toArray( $templateString ) {
        $toReplace = array("{", "}", "|", "[", "]", " ");
        return explode(",", str_replace($toReplace, "", $templateString));
    }

    public static function toStringWithoutBrackets( $templateString ) {
        $toReplace = array("{", "}", "|", "[", "]");
        return str_replace( $toReplace, '', $templateString);
    }
}