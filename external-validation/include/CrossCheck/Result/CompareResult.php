<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Result;

use DataValues\DataValue;
use Wikibase\DataModel\Entity\PropertyId;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use Doctrine\Instantiator\Exception\InvalidArgumentException;


/**
 * Class CompareResult
 * @package WikidataQuality\ExternalValidation\CrossCheck\Result
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CompareResult
{
    /**
     * Id of the property of the claim, that was compared.
     * @var PropertyId
     */
    private $propertyId;

    /**
     * Id of the claim, that was compared.
     * @var string
     */
    private $claimGuid;

    /**
     * Wikibase data value
     * @var DataValue
     */
    private $localValue;

    /**
     * Data values from external database.
     * @var array
     */
    private $externalValues;

    /**
     * Determines, whether a data mismatch occurred.
     * @var bool
     */
    private $dataMismatch;

    /**
     * Determines, whether references are missing.
     * @var bool
     */
    private $referencesMissing;

    /**
     * Meta information about the data source.
     * @var DumpMetaInformation
     */
    private $dumpMetaInformation;


    /**
     * @param $propertyId
     * @param $claimGuid
     * @param DataValue $localValue
     * @param array $externalValues
     * @param bool $dataMismatch
     * @param bool $referencesMissing
     * @param DumpMetaInformation $dumpMetaInformation
     * @throws InvalidArgumentException
     */
    public function __construct( $propertyId, $claimGuid, $localValue, $externalValues, $dataMismatch, $referencesMissing, $dumpMetaInformation )
    {
        if ( $propertyId instanceof PropertyId ) {
            $this->propertyId = $propertyId;
        } else {
            throw new InvalidArgumentException( '$propertyId must be an instance of PropertyId.' );
        }

        $this->claimGuid = $claimGuid;

        if ( $localValue instanceof DataValue ) {
            $this->localValue = $localValue;
        } else {
            throw new InvalidArgumentException( '$localValue must be an instance of DataValue.' );
        }

        if ( is_array( $externalValues ) ) {
            foreach ( $externalValues as $externalValue ){
                if ( $externalValue instanceof DataValue ){

                } else {
                    throw new InvalidArgumentException( 'An external value must be instance of DataValue.' );
                }
            }
            $this->externalValues = $externalValues;
        } else {
            throw new InvalidArgumentException( '$externalValues must be an array.' );
        }

        $this->dataMismatch = $dataMismatch;
        $this->referencesMissing = $referencesMissing;
        $this->dumpMetaInformation = $dumpMetaInformation;
    }

    /**
     * @return PropertyId
     */
    public function getPropertyId()
    {
        return $this->propertyId;
    }

    /**
     * @return string
     */
    public function getClaimGuid()
    {
        return $this->claimGuid;
    }

    /**
     * @return DataValue
     */
    public function getLocalValue()
    {
        return $this->localValue;
    }

    /**
     * @return array
     */
    public function getExternalValues()
    {
        return $this->externalValues;
    }

    /**
     * @return bool
     */
    public function hasDataMismatchOccurred()
    {
        return $this->dataMismatch;
    }

    /**
     * @return bool
     */
    public function areReferencesMissing()
    {
        return $this->referencesMissing;
    }

    /**
     * @return DumpMetaInformation
     */
    public function getDumpMetaInformation()
    {
        return $this->dumpMetaInformation;
    }
}