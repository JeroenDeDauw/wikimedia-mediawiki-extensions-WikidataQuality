<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Result;

use ArrayIterator;
use Countable;
use IteratorAggregate;


/**
 * Class CompareResultList
 * @package WikidataQuality\ExternalValidation\CrossCheck\Result
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CompareResultList implements IteratorAggregate, Countable
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
     * Adds a given CompareResult to the list.
     * @param CompareResult $result
     */
    public function add( CompareResult $result )
    {
        $this->results[ ] = $result;
    }

    /**
     * Merges another CompareResultList to the current one.
     * @param CompareResultList $list
     */
    public function merge( CompareResultList $resultList )
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
     * Specifies, whether at least one reference is missing.
     * @return bool
     */
    public function areReferencesMissing()
    {
        foreach ( $this->results as $result ) {
            if ( $result->areReferencesMissing() ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the property ids used by compare results.
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
     * Returns all compare results using given property id.
     * @param $propertyId
     * @return CompareResultList
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