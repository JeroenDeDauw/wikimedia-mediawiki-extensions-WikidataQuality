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
     * Id of the claim, that was compared
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
     * @param array $localValues
     * @param array $externalValues
     * @param bool $dataMismatch
     * @param bool $referencesMissing
     */
    public function __construct( $propertyId, $claimGuid, $localValues, $externalValues, $dataMismatch, $referencesMissing ) {
        $this->propertyId = $propertyId;
        $this->claimGuid = $claimGuid;
        $this->localValues = $localValues;
        $this->externalValues = $externalValues;
        $this->dataMismatch = $dataMismatch;
        $this->referencesMissing = $referencesMissing;
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

    public function isDataMismatchOccurred() {
        return $this->isDataMismatchOccurred();
    }

    public function areReferencesMissing() {
        return $this->referencesMissing;
    }
}