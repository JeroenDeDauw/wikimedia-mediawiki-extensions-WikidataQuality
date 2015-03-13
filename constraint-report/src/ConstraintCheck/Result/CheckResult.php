<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Result;

class CheckResult {

    private $propertyId;
    private $dataValue;
    private $constraintName;
    private $parameters;
    private $status;

    public function __construct( $propertyId, $dataValue, $constraintName, $parameters, $status ) {
        $this->propertyId = $propertyId;
        $this->dataValue = $dataValue;
        $this->constraintName = $constraintName;
        $this->parameters = $parameters;
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

    public function getParameters() {
        return $this->parameters;
    }

    public function getStatus() {
        return $this->status;
    }

}