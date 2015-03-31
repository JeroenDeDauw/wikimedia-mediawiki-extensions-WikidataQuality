<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Result;

use ArrayIterator;
use Countable;
use IteratorAggregate;


/**
 * Class CrossCheckResultList
 * @package WikidataQuality\ExternalValidation\CrossCheck\Result
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CrossCheckResultList implements IteratorAggregate, Countable
{
    private $results;


    /**
     * @param array $list
     */
    public function __construct( $results = array() )
    {
        $this->results = $results;
    }


    /**
     * Adds a given CrossCheckResult to the list.
     * @param CrossCheckResult $result
     */
    public function add( CrossCheckResult $result )
    {
        $this->results[ ] = $result;
    }

    /**
     * Merges another CrossCheckResultList to the current one.
     * @param CrossCheckResultList $list
     */
    public function merge( CrossCheckResultList $resultList )
    {
        $this->results = array_merge( $this->results, $resultList->results );
    }


    /**
     * Specifies, whether at least one data mismatch occurred.
     * @return bool
     */
    public function hasDataMismatchOccurred()
    {
        foreach ( $this->results as $result ) {
            if ( $result->hasDataMismatchOccurred() ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the property ids used by crosscheck results.
     * @return array
     */
    function getPropertyIds()
    {
        $propertyIds = array();

        foreach ( $this->results as $result ) {
            $propertyId = $result->getPropertyId();
            if ( !in_array( $propertyId, $propertyIds ) ) {
                $propertyIds[ ] = $propertyId;
            }
        }

        return $propertyIds;
    }

    /**
     * Returns all crosscheck results using given property id.
     * @param $propertyId
     * @return CrossCheckResultList
     */
    function getWithPropertyId( $propertyId )
    {
        $results = array();

        foreach ( $this->results as $result ) {
            if ( $result->getPropertyId()->equals( $propertyId ) ) {
                $results[ ] = $result;
            }
        }

        return new self( $results );
    }

    /**
     * Gets an iterator for results.
     * @return ArrayIterator
     * @codeCoverageIgnore
     */
    public function getIterator()
    {
        return new ArrayIterator( $this->results );
    }

    /**
     * Counts number of results.
     * @return int
     */
    public function count()
    {
        return count( $this->results );
    }
}