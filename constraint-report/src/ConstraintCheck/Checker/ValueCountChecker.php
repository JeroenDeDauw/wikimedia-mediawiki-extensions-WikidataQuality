<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use Wikibase\DataModel\Statement\Statement;
use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class ValueCountChecker {

    private $propertyCount;
    private $statements;
    private $helper;

    public function __construct( $statements, $helper ) {
        $this->statements = $statements;
        $this->helper = $helper;
    }

    public function checkSingleValueConstraint( $propertyId, $dataValueString ) {
        $parameters = array();

        if( $this->getPropertyCount( $this->statements )[$propertyId->getNumericId()] > 1 ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        return new CheckResult( $propertyId, $dataValueString, 'Single value', $parameters, $status );
    }

    public function checkMultiValueConstraint( $propertyId, $dataValueString ) {
        $parameters = array();

        if( $this->getPropertyCount( $this->statements )[$propertyId->getNumericId()] <= 1 ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        return new CheckResult( $propertyId, $dataValueString, 'Multi value', $parameters, $status );
    }

    // TODO: implement when index exists that makes it possible in real-time
    public function checkUniqueValueConstraint( $propertyId, $dataValueString ) {
        $parameters = array();

        return new CheckResult( $propertyId, $dataValueString, 'Unique value', $parameters, 'todo' );
    }

    private function getPropertyCount( $statements )
    {
        if ( !isset( $propertyCount ) ) {
            $this->propertyCount = array();
            foreach( $statements as $statement ) {
                if( $statement->getRank() === Statement::RANK_DEPRECATED ) {
                    continue;
                }

                if( array_key_exists( $statement->getPropertyId()->getNumericId(), $this->propertyCount ) ) {
                    $this->propertyCount[$statement->getPropertyId()->getNumericId()]++;
                } else {
                    $this->propertyCount[$statement->getPropertyId()->getNumericId()] = 1;
                }
            }
        }
        return $this->propertyCount;
    }
}