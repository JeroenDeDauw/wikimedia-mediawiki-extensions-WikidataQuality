<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Result;

use Wikibase\DataModel\Entity\PropertyId;
use DataValues\DataValue;

/**
 * Class CheckResult
 * Used for getting information about the result of a constraint check
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Result
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CheckResult {

    /**
     * @var PropertyId
     */
    private $propertyId;

    /**
     * @var DataValue
     */
    private $dataValue;

    /**
     * @var string
     */
    private $constraintName;

    /**
     * @var Array
     * Includes arrays of ItemIds or PropertyIds or strings.
     */
    private $parameters;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    public function __construct( $propertyId, $dataValue, $constraintName, $parameters = array(), $status = 'todo', $message = '' ) {
        $this->propertyId = $propertyId;
        $this->dataValue = $dataValue;
        $this->constraintName = $constraintName;
        $this->parameters = $parameters;
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * @return PropertyId
     */
    public function getPropertyId() {
        return $this->propertyId;
    }

    /**
     * @return DataValue
     */
    public function getDataValue() {
        return $this->dataValue;
    }

    /**
     * @return string
     */
    public function getConstraintName() {
        return $this->constraintName;
    }

    /**
     * @return Array
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

}