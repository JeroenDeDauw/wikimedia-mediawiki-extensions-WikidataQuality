<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\OutputLimiter;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\TemplateConverter;
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

    public function checkConflictsWithConstraint( $propertyId, $dataValueString, $list) {
        $toReplace = array("{", "}", "|", " ");
        $listArray = explode(';', str_replace($toReplace, '', $list));
        $parameterString = $this->helper->limitOutput( str_replace($toReplace, '', $list) );
        foreach( $listArray as $conflictingValues ) {
            if ( stripos($conflictingValues, ':') === false) {
                $status = $this->hasProperty( $this->statements, $conflictingValues ) ? 'violation' : 'compliance';
                if( $status == 'compliance')
                    break;
            } else {
                $subArray = explode(':', $conflictingValues);
                $property = $subArray[0];
                $subArray = $subArray[1];
                $subArray = explode(',', $subArray);
                $status = $this->hasClaim( $this->statements, $property, $subArray ) ? 'violation' : 'compliance';
                if( $status == 'compliance')
                    break;
            }
        }
        return new CheckResult( $propertyId, $dataValueString, 'Conflicts with', $parameterString, $status );
    }

    public function checkItemConstraint( $propertyId, $dataValueString, $property, $item, $items ) {
        $parameterString = 'property: ' . $property;
        if( $item == null && $items == null ){
            $status = $this->hasProperty( $this->statements, $property ) ? 'compliance' : 'violation';
        } elseif ($items == null ) {
            $parameterString .= ' item: ' . $item;
            $status = $this->hasClaim($this->statements, $property, $item) ? 'compliance' : 'violation';
        } else {
            $items = $this->helper->toArray( $items );
            $parameterString .= ' items: ' . implode(', ', $items );
            $status = $this->hasClaim($this->statements, $property, $items) ? 'compliance' : 'violation';
        }
        return new CheckResult( $propertyId, $dataValueString, "Item", $parameterString, $status );
    }

    public function checkTargetRequiredClaimConstraint( $propertyId, $dataValueString, $property, $item, $items) {
        $targetItem = $this->entityLookup->getEntity( new ItemId( $dataValueString->getSerialization() ));
        $parameterString = 'property: ' . $property;
        if ($targetItem == null) {
            return new CheckResult($propertyId, $dataValueString, "Target required claim", $parameterString, "fail" );
        }

        $targetItemStatements = $targetItem->getStatements();
        $targetItemStatementsArray = $targetItemStatements->toArray();

        // 3 possibilities: only property is set, property and item are set or property and items are set
        if ($item == null && $items == null) {
            $targetHasProperty = $this->hasProperty( $targetItemStatementsArray, $property );
            $status = $targetHasProperty ? 'compliance' : 'violation';
        } else if ($items == null) {
            $parameterString .= ' item: ' . $item;
            // also check, if value of this statement = $item
            $status = $this->hasClaim( $targetItemStatementsArray, $property, $item ) ? 'compliance' : 'violation';
        } else {
            $items = $this->helper->toArray( $items );
            $parameterString .= ' items: ' . implode(', ', $items);
            $status = $this->hasClaim( $targetItemStatementsArray, $property, $items ) ? 'compliance' : 'violation';
        }

        return new CheckResult($propertyId, $dataValueString, "Target required claim", $parameterString, $status );
    }

    public function checkSymmetricConstraint( $propertyId, $dataValueString ) {
        $targetItem = $this->entityLookup->getEntity( new ItemId( $dataValueString->getSerialization() ));
        if ($targetItem == null) {
            return new CheckResult($propertyId, $dataValueString, "Symmetric", '\'\'(none)\'\'', "fail");
        }

        $targetItemStatements = $targetItem->getStatements();
        $targetItemStatementsArray = $targetItemStatements->toArray();

        $targetHasProperty = $this->hasProperty( $targetItemStatementsArray, $propertyId );
        $status = $targetHasProperty ? 'compliance' : 'violation';

        return new CheckResult($propertyId, $dataValueString, "Symmetric", '\'\'(none)\'\'', $status );
    }

    public function checkInverseConstraint( $propertyId, $dataValueString, $property) {
        $targetItem = $this->entityLookup->getEntity( new ItemId( $dataValueString->getSerialization() ));
        $parameterString = 'Property: ' . $property;
        if ($targetItem == null) {
            return new CheckResult($propertyId, $dataValueString, "Inverse", $parameterString, "fail" );
            return;
        }
        $targetItemStatements = $targetItem->getStatements();
        $targetItemStatementsArray = $targetItemStatements->toArray();

        $targetHasProperty = $this->hasProperty( $targetItemStatementsArray, $property );
        $status = $targetHasProperty ? 'compliance' : 'violation';

        return new CheckResult($propertyId, $dataValueString, "Inverse", $parameterString, $status );
    }


    private function hasProperty( $itemStatementsArray, $propertyId ) {
        foreach( $itemStatementsArray as $itemStatement ) {
            if ($itemStatement->getPropertyId() == $propertyId){
                return true;
            }
        }
        return false;
    }

    private function hasClaim( $itemStatementsArray, $propertyId, $claimItemIdOrArray ) {
        foreach( $itemStatementsArray as $itemStatement ) {
            if ($itemStatement->getPropertyId() == $propertyId){
                if (getType($claimItemIdOrArray) == "string" ) {
                    if ($this->singleHasClaim( $itemStatement, $claimItemIdOrArray)){
                        return true;
                    }
                } else {
                    if ($this->arrayHasClaim( $itemStatement, $claimItemIdOrArray)){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function singleHasClaim( $itemStatement, $claimItemId) {
        if ( $itemStatement->getClaim()->getMainSnak()->getDataValue()->getEntityId()->getSerialization() == $claimItemId) {
            return true;
        }
        return false;
    }

    private function arrayHasClaim( $itemStatement, $claimItemIdArray) {
        foreach( $claimItemIdArray as $claimItemId) {
            if ( $itemStatement->getClaim()->getMainSnak()->getDataValue()->getEntityId()->getSerialization() == $claimItemId) {
                return true;
            }
        }
        return false;
    }
}