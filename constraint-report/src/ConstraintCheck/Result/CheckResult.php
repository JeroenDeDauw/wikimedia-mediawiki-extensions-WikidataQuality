<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Result;

class CheckResult {

    private $propertyId;
    private $dataValue;
    private $constraintName;
    private $parameters;
    private $status;
    private $message;

    public function __construct( $propertyId, $dataValue, $constraintName, $parameters = array(), $status = 'error', $message = '' ) {
        $this->propertyId = $propertyId;
        $this->dataValue = $dataValue;
        $this->constraintName = $constraintName;
        $this->parameters = $parameters;
        $this->status = $status;
        $this->message = $message;
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

    public function getMessage() {
        return $this->message;
    }

}