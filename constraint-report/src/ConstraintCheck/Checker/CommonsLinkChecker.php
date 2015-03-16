<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class CommonsLinkChecker {

    private $helper;

    public function __construct( $helper ) {
        $this->helper = $helper;
    }

    public function checkCommonsLinkConstraint( $propertyId, $dataValue, $namespace ) {
        $parameters = array();

        if( $namespace == null ) {
            $parameters['namespace'] = array( 'null' );
        } else {
            $parameters['namespace'] = array( $namespace );
        }

        /*
         * error handling:
         *   type of $dataValue for properties with 'Commons link' constraint has to be 'string'
         *   parameter $namespace can be null, works for commons galleries
         */
        if( $dataValue->getType() !== 'string' ) {
            return new CheckResult( $propertyId, $dataValue, 'Commons link', $parameters, 'error' );
        }

        $commonsLink = $dataValue->getValue();

        if( $this->commonsLinkIsWellFormed( $commonsLink ) ) {
            $status = $this->urlExists( $commonsLink, $namespace ) ? 'compliance' : 'violation';
        } else {
            $status = 'violation';
        }

        return new CheckResult( $propertyId, $dataValue, 'Commons link', $parameters, $status );
    }

    private function urlExists( $commonsLink, $namespace ) {
        $responseCode = substr( get_headers( 'http://commons.wikimedia.org/wiki/' . $namespace . ':' . str_replace( ' ', '_', $commonsLink ) )[0], 9, 3);
        return $responseCode < 400;
    }

    private function commonsLinkIsWellFormed( $commonsLink ) {
        $toReplace = array( "_", ":", "%20" );
        $compareString = trim( str_replace( $toReplace, '', $commonsLink ) );
        return $commonsLink == $compareString;
    }

}