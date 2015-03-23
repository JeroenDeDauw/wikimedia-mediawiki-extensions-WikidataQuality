<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

/**
 * Class CommonsLinkChecker.
 * Checks 'Commons link' constraint.
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Checker
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CommonsLinkChecker {

    /**
     * Class for helper functions for constraint checkers.
     * @var ConstraintReportHelper
     */
    private $helper;

    /**
     * @param $helper
     */
    public function __construct( $helper ) {
        $this->helper = $helper;
    }

    /**
     * Checks if data value is well-formed and links to an existing page.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param string $namespace
     * @return CheckResult
     */
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
            $message = 'Properties with \'Commons link\' constraint need to have values of type \'string\'.';
            return new CheckResult( $propertyId, $dataValue, 'Commons link', $parameters, 'violation', $message );
        }

        $commonsLink = $dataValue->getValue();

        if( $this->commonsLinkIsWellFormed( $commonsLink ) ) {
            if( $this->urlExists( $commonsLink, $namespace ) ) {
                $message = '';
                $status = 'compliance';
            } else {
                $message = 'Commons link must exist.';
                $status = 'violation';
            }
        } else {
            $message = 'Commons link must be well-formed.';
            $status = 'violation';
        }

        return new CheckResult( $propertyId, $dataValue, 'Commons link', $parameters, $status, $message );
    }

    /**
     * @param string $commonsLink
     * @param string $namespace
     * @return bool
     */
    private function urlExists( $commonsLink, $namespace ) {
        $responseCode = substr( get_headers( 'http://commons.wikimedia.org/wiki/' . $namespace . ':' . str_replace( ' ', '_', $commonsLink ) )[0], 9, 3);
        return $responseCode < 400;
    }

    /**
     * @param string $commonsLink
     * @return bool
     */
    private function commonsLinkIsWellFormed( $commonsLink ) {
        $toReplace = array( "_", ":", "%20" );
        $compareString = trim( str_replace( $toReplace, '', $commonsLink ) );
        return $commonsLink === $compareString;
    }

}