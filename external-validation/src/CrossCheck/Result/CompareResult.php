<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Result;


use JsonSerializable;


/**
 * Class CompareResult
 * @package WikidataQuality\ExternalValidation\CrossCheck\Result
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CompareResult {
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
     * Array of compared local values.
     * @var array
     */
    private $localValues;

    /**
     * Array of compared external values.
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
     * @param array $localValues
     * @param array $externalValues
     * @param bool $dataMismatch
     * @param bool $referencesMissing
     * @param string $dataSourceName
     */
    public function __construct( $propertyId, $claimGuid, $localValues, $externalValues, $dataMismatch, $referencesMissing, $dataSourceName ) {
        $this->propertyId = $propertyId;
        $this->claimGuid = $claimGuid;
        $this->localValues = $localValues;
        $this->externalValues = $externalValues;
        $this->dataMismatch = $dataMismatch;
        $this->referencesMissing = $referencesMissing;
        $this->dataSourceName = $dataSourceName;
    }

    /**
     * @return \PropertyId
     */
    public function getPropertyId() {
        return $this->propertyId;
    }

    /**
     * @return string
     */
    public function getClaimGuid() {
        return $this->claimGuid;
    }

    /**
     * @return array
     */
    public function getLocalValues() {
        return $this->localValues;
    }

    /**
     * @return array
     */
    public function getExternalValues() {
        return $this->externalValues;
    }

    /**
     * @return bool
     */
    public function hasDataMismatchOccurred() {
        return $this->dataMismatch;
    }

    /**
     * @return bool
     */
    public function areReferencesMissing() {
        return $this->referencesMissing;
    }

    /**
     * @return string
     */
    public function getDataSourceName() {
        return $this->dataSourceName;
    }
}