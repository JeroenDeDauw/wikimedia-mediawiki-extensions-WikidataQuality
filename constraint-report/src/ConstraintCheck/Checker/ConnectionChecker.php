<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\ItemId;

class ConnectionChecker {

    private $statements;
    private $entityLookup;
    private $helper;

    public function __construct( $statements, $lookup, $helper  ) {
        $this->statements = $statements;
        $this->entityLookup = $lookup;
        $this->helper = $helper;
    }

    public function checkConflictsWithConstraint( $propertyId, $dataValueString, $property, $itemArray ) {
        $parameterString = 'property: ' . $property;

        if( empty( $itemArray ) ) {
            $status = $this->hasProperty( $this->statements, $property ) ? 'violation' : 'compliance';
        } else {
            $status = $this->hasClaim( $this->statements, $property, $itemArray ) ? 'violation' : 'compliance';
            $parameterString .= ( ', item: ' . $this->helper->arrayToString( $itemArray ) );
        }

        return new CheckResult( $propertyId, $dataValueString, 'Conflicts with', $parameterString, $status );
    }

    public function checkItemConstraint( $propertyId, $dataValueString, $property, $itemArray ) {
        $parameterString = 'property: ' . $property;

        if( empty( $itemArray ) ) {
            $status = $this->hasProperty( $this->statements, $property ) ? 'compliance' : 'violation';
        } else {
            $status = $this->hasClaim( $this->statements, $property, $itemArray ) ? 'compliance' : 'violation';
            $parameterString .= ( ', item: ' . $this->helper->arrayToString( $itemArray ) );
        }

        return new CheckResult( $propertyId, $dataValueString, 'Item', $parameterString, $status );
    }

    public function checkTargetRequiredClaimConstraint( $propertyId, $dataValueString, $property, $itemArray ) {
        $parameterString = 'property: ' . $property;

        $targetItem = $this->entityLookup->getEntity( new ItemId( $dataValueString ) );
        if( $targetItem == null ) {
            return new CheckResult( $propertyId, $dataValueString, 'Target required claim', $parameterString, 'fail' );
        }

        $targetItemStatementsArray = $targetItem->getStatements()->toArray();

        if( empty( $itemArray ) ) {
            $status = $this->hasProperty( $targetItemStatementsArray, $property ) ? 'compliance' : 'violation';
        } else {
            $status = $this->hasClaim( $targetItemStatementsArray, $property, $itemArray ) ? 'compliance' : 'violation';
            $parameterString .= ( ', item: ' . $this->helper->arrayToString( $itemArray ) );
        }

        return new CheckResult( $propertyId, $dataValueString, 'Target required claim', $parameterString, $status );
    }

    public function checkSymmetricConstraint( $propertyId, $dataValueString ) {
        $targetItem = $this->entityLookup->getEntity( new ItemId( $dataValueString ) );

        if( $targetItem == null ) {
            return new CheckResult( $propertyId, $dataValueString, 'Symmetric', '(none)', 'fail' );
        }

        $targetItemStatementsArray = $targetItem->getStatements()->toArray();

        $status = $this->hasProperty( $targetItemStatementsArray, $propertyId ) ? 'compliance' : 'violation';

        return new CheckResult( $propertyId, $dataValueString, 'Symmetric', '(none)', $status );
    }

    public function checkInverseConstraint( $propertyId, $dataValueString, $property ) {
        $parameterString = 'property: ' . $property;

        $targetItem = $this->entityLookup->getEntity( new ItemId( $dataValueString ) );

        if( $targetItem == null ) {
            return new CheckResult( $propertyId, $dataValueString, 'Inverse', $parameterString, 'fail' );
        }

        $targetItemStatementsArray = $targetItem->getStatements()->toArray();

        $status = $this->hasProperty( $targetItemStatementsArray, $property ) ? 'compliance' : 'violation';

        return new CheckResult($propertyId, $dataValueString, 'Inverse', $parameterString, $status );
    }


    private function hasProperty( $itemStatementsArray, $propertyId ) {
        foreach( $itemStatementsArray as $itemStatement ) {
            if( $itemStatement->getPropertyId() == $propertyId ) {
                return true;
            }
        }
        return false;
    }

    private function hasClaim( $itemStatementsArray, $propertyId, $claimItemIdOrArray ) {
        foreach( $itemStatementsArray as $itemStatement ) {
            if( $itemStatement->getPropertyId() == $propertyId ) {
                if( getType( $claimItemIdOrArray ) == "string" ) {
                    if( $this->singleHasClaim( $itemStatement, $claimItemIdOrArray ) ) {
                        return true;
                    }
                } else {
                    if( $this->arrayHasClaim( $itemStatement, $claimItemIdOrArray ) ) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function singleHasClaim( $itemStatement, $claimItemId ) {
        if( $this->helper->getDataValueString( $itemStatement->getClaim() ) == $claimItemId ) {
            return true;
        }
        return false;
    }

    private function arrayHasClaim( $itemStatement, $claimItemIdArray ) {
        foreach( $claimItemIdArray as $claimItemId ) {
            if( $this->helper->getDataValueString( $itemStatement->getClaim() ) == $claimItemId ) {
                return true;
            }
        }
        return false;
    }
}