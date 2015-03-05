<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use ValueFormatters\FormatterOptions;
use Wikibase\Lib\CommonsLinkFormatter;
use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class CommonsLinkChecker {

    private $helper;

    public function __construct( $helper ) {
        $this->helper = $helper;
    }

    public function checkCommonsLinkConstraint( $propertyId, $dataValueString, $namespace ) {
        if( $this->isCommonsLinkWellFormed( $dataValueString ) )
            $status = $this->url_exists( $dataValueString, $namespace ) ? 'compliance' : 'violation';
        else
            $status = 'violation';
        return new CheckResult( $propertyId, $dataValueString, 'Commons link', 'namespace: ' . $namespace, $status );
    }

    private function url_exists( $dataValueString, $namespace )
    {
        $responseCode = substr( get_headers( 'http://commons.wikimedia.org/wiki/' . $namespace . ':' . str_replace( ' ', '_', $dataValueString ) )[0], 9, 3);
        return $responseCode < 400;
    }

    private function isCommonsLinkWellFormed( $dataValueString ) {
        $toReplace = array("_", ":", "%20");
        $compareString = trim( str_replace( $toReplace, '', $dataValueString) );
        return $dataValueString == $compareString;
    }

}