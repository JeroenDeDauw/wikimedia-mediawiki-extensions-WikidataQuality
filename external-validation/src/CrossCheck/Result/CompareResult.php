<?php
/**
 * Created by PhpStorm.
 * User: soldag
 * Date: 27.01.15
 * Time: 22:47
 */

namespace WikidataQuality\ExternalValidation\CrossCheck\Result;


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

    public function getPropertyId() {
        return $this->propertyId;
    }

    public function getClaimGuid() {
        return $this->claimGuid;
    }

    public function getLocalValues() {
        return $this->localValues;
    }

    public function getExternalValues() {
        return $this->externalValues;
    }

    public function hasDataMismatchOccurred() {
        return $this->dataMismatch;
    }

    public function areReferencesMissing() {
        return $this->referencesMissing;
    }

    public function getDataSourceName() {
        return $this->dataSourceName;
    }
}