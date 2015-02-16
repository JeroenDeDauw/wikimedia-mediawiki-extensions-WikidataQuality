<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Helper;


class OutputLimiter {
    private static $showMax = 50;

    public static function limitOutput( $string ) {
        if( strlen($string) <= self::$showMax ) {
            return $string;
        } else {
            return substr( $string, 0, self::$showMax ) . '...';
        }
    }

}