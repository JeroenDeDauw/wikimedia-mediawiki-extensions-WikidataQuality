<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use ValueFormatters\FormatterOptions;
use Wikibase\Lib\CommonsLinkFormatter;
use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class CommonsLinkChecker {

    public function checkCommonsLinkConstraint( $propertyId, $dataValueString ) {
        if( $this->isCommonsLinkWellFormed( $dataValueString ) )
            $status = $this->url_exists( $dataValueString ) ? 'compliance' : 'violation';
        else
            $status = 'violation';
        return new CheckResult($propertyId, $dataValueString, 'Commons link', '\'\'(none)\'\'', $status );
    }

    private function url_exists( $dataValueString ) {
        //$formatter = new CommonsLinkFormatter( new FormatterOptions( array('What should I pass here' => '???' ) ) );
        //$file = $formatter->format( $dataValueString );

        $responseCode1 = substr( get_headers('http://commons.wikimedia.org/wiki/File:' . str_replace(' ', '_', $dataValueString ))[0], 9, 3);
        $responseCode2 = substr( get_headers('http://commons.wikimedia.org/wiki/Category:' . str_replace(' ', '_', $dataValueString ))[0], 9, 3);
        return $responseCode1 < 400 || $responseCode2 < 400;
    }

    private function isCommonsLinkWellFormed( $dataValueString ) {
        $toReplace = array("_", ":", "%20");
        $compareString = trim( str_replace( $toReplace, '', $dataValueString) );
        return $dataValueString == $compareString;
    }

}