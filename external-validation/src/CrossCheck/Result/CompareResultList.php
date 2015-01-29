<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Result;

use IteratorAggregate;
use Countable;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;


/**
 * Class CompareResultList
 * @package WikidataQuality\ExternalValidation\CrossCheck\Result
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CompareResultList implements IteratorAggregate, Countable
{
    private $results = array();

    /**
     * Adds a given CompareResult to the list.
     * @param CompareResult $result
     */
    public function add( CompareResult $result ) {
        $this->results[] = $result;
    }

    /**
     * Merges another CompareResultList to the current one.
     * @param CompareResultList $list
     */
    public function merge( CompareResultList $list ) {
        $this->results = array_merge( $this->results, $list->results );
    }


    /**
     * Specifies, whether at least one data mismatch occurred.
     * @return bool
     */
    public function isDataMismatchOccurred() {
        foreach ( $this->results as $result ) {
            if ( $result->isDataMismatchOccurred() ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Specifies, whether at least one reference is missing.
     * @return bool
     */
    public function areReferencesMissing() {
        foreach ( $this->results as $result ) {
            if ( $result->areReferencesMissing() ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets subset of results matching the given filters.
     * @param bool|null $dataMismatch
     * @param bool|null $referencesMissing
     * @return array
     */
    public function getResults( $dataMismatch = null, $referencesMissing = null )
    {
        $output = array();

        foreach ( $this->results as $result ) {
            if  ( ( isset( $dataMismatch ) && isset( $referencesMissing ) && $result->isDataMismatchOccurred() == $dataMismatch && $result->areReferencesMissing() == $referencesMissing ) ||
                  ( isset( $dataMismatch ) && !isset( $referencesMissing ) && $result->isDataMismatchOccurred() == $dataMismatch ) ||
                  ( !isset( $dataMismatch ) && isset( $referencesMissing ) && $result->areReferencesMissing() == $referencesMissing ) ||
                  ( !isset( $dataMismatch ) && !isset( $referencesMissing ) ) )
            {
                $output[] = $result;
            }
        }

        return $output;
    }

    /**
     * Gets an iterator for results.
     * @return ArrayIterator
     */
    public function getIterator() {
        return new ArrayIterator( $this->results );
    }

    /**
     * Counts number of results.
     * @return int
     */
    public function count () {
        return count( $this->results );
    }
}