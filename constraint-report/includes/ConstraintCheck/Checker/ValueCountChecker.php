<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use Wikibase\DataModel\Statement\Statement;
use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

/**
 * Class ValueCountChecker.
 * Checks 'Single', 'Multi' and 'Unique value' constraint.
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Checker
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class ValueCountChecker {

    private $propertyCount;

    /**
     * List of all statemtens of given entity.
     * @var StatementList
     */
    private $statements;

    /**
     * Class for helper functions for constraint checkers.
     * @var ConstraintReportHelper
     */
    private $helper;

    /**
     * @param StatementList $statements
     * @param ConstraintReportHelper $helper
     */
    public function __construct( $statements, $helper ) {
        $this->statements = $statements;
        $this->helper = $helper;
    }

    /**
     * Checks 'Single value' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @return CheckResult
     */
    public function checkSingleValueConstraint( $propertyId, $dataValue ) {
        $parameters = array();
        $propertyCountArray = $this->getPropertyCount( $this->statements );
        if( $propertyCountArray[$propertyId->getNumericId()] > 1 ) {

            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        return new CheckResult( $propertyId, $dataValue, 'Single value', $parameters, $status );
    }

    /**
     * Checks Multi value constraint
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @return CheckResult
     */
    public function checkMultiValueConstraint( $propertyId, $dataValue ) {
        $parameters = array();
        $propertyCountArray = $this->getPropertyCount( $this->statements );
        if( $propertyCountArray[$propertyId->getNumericId()] <= 1 ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        return new CheckResult( $propertyId, $dataValue, 'Multi value', $parameters, $status );
    }

    // TODO: implement when index exists that makes it possible in real-time
    /**
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @return CheckResult
     */
    public function checkUniqueValueConstraint( $propertyId, $dataValue ) {
        $parameters = array();

        return new CheckResult( $propertyId, $dataValue, 'Unique value', $parameters, 'todo' );
    }

    private function getPropertyCount( $statements )
    {
        if ( !isset( $this->propertyCount ) ) {
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