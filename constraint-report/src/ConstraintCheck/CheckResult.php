<?php

namespace WikidataQuality\ConstraintChecker;

class CheckResult {

    private $propertyId;
    private $dataValue;
    private $constraintName;
    private $parameter;
    private $status;

    public function __construct( $propertyId, $dataValue, $constraintName, $parameter, $status ) {
        $this->propertyId = $propertyId;
        $this->dataValue = $dataValue;
        $this->constraintName = $constraintName;
        $this->parameter = $parameter;
        $this->status = $status;
    }

    public function getPropertyId() {
        return $this->propertyId;
    }

    public function getDataValue() {
        return $this->dataValue;
    }

    public function getConstraintName() {
        return $this->constraintName;
    }

    public function getParameter() {
        return $this->parameter;
    }

    public function getStatus() {
        return $this->status;
    }

}