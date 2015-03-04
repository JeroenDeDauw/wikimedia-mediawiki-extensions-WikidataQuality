<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Result;


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
     * @var \PropertyId
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
     * Name of data source.
     * @var string
     */
    private $dataSourceName;


    /**
     * @param $propertyId
     * @param $claimGuid
     * @param DataValue $localValue
     * @param array $externalValues
     * @param bool $dataMismatch
     * @param bool $referencesMissing
     * @param string $dataSourceName
     */
    public function __construct( $propertyId, $claimGuid, $localValue, $externalValues, $dataMismatch, $referencesMissing, $dataSourceName )
    {
        $this->propertyId = $propertyId;
        $this->claimGuid = $claimGuid;
        $this->localValue = $localValue;
        $this->externalValues = $externalValues;
        $this->dataMismatch = $dataMismatch;
        $this->referencesMissing = $referencesMissing;
        $this->dataSourceName = $dataSourceName;
    }

    /**
     * @return \PropertyId
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
     * @return string
     */
    public function getDataSourceName()
    {
        return $this->dataSourceName;
    }
}