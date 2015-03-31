<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Result;

/**
 * Class CrossCheckResult
 * @package WikidataQuality\ExternalValidation\CrossCheck\Result
 * @author BP2014N1
 * @license GNU GPL v2+
 */

class CrossCheckResult
{
    private $compareResult;
    private $referenceResult;

    public function __construct( CompareResult $compareResult, ReferenceResult $referenceResult )
    {
        $this->compareResult = $compareResult;
        $this->referenceResult = $referenceResult;
    }

    /**
     * @return PropertyId
     */
    public function getPropertyId()
    {
        return $this->compareResult->getPropertyId();
    }

    /**
     * @return string
     */
    public function getClaimGuid()
    {
        return $this->compareResult->getClaimGuid();
    }

    /**
     * @return DataValue
     */
    public function getLocalValue()
    {
        return $this->compareResult->getLocalValue();
    }

    /**
     * @return array
     */
    public function getExternalValues()
    {
        return $this->compareResult->getExternalValues();
    }

    /**
     * @return bool
     */
    public function hasDataMismatchOccurred()
    {
        return $this->compareResult->hasDataMismatchOccurred();
    }


    /**
     * @return DumpMetaInformation
     */
    public function getDumpMetaInformation()
    {
        return $this->compareResult->getDumpMetaInformation();
    }

    /**
     * @return bool
     */
    public function areReferencesMissing()
    {
        return $this->referenceResult->areReferencesMissing();
    }

    /**
     * @return Reference
     */
    public function getAddableReference()
    {
        return $this->referenceResult->getAddableReference();
    }
}