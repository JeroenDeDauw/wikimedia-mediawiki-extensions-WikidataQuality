<?php

class ConnectionChecker {

    private $statements;
    private $entityLookup;

    public function __construct( $statements, $lookup) {
        $this->statements = $statements;
        $this->entityLookup = $lookup;
    }

    function checkTargetRequiredClaimConstraint( $propertyId, $dataValueString, $property, $item, $items) {
        $targetItem = $this->entityLookup->getEntity( new EntityId( $dataValueString->getSerialization() ));
        $parameterString = 'property: ' . $property;
        if ($targetItem == null) {
            return new \CheckResult($propertyId, $dataValueString, "Target required claim", $parameterString, "fail" );
            return;
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
            $items = $this->convertStringFromTemplatesToArray( $items );
            $parameterString .= ' items: ' . implode(', ', $items);
            $status = $this->hasClaim( $targetItemStatementsArray, $property, $items ) ? 'compliance' : 'violation';
        }

        return new \CheckResult($propertyId, $dataValueString, "Target required claim", $parameterString, $status );
    }

    public function checkSymmetricConstraint( $propertyId, $dataValueString ) {
        $targetItem = $this->entityLookup->getEntity( new EntityId( $dataValueString->getSerialization() ));
        if ($targetItem == null) {
            return new \CheckResult($propertyId, $dataValueString, "Symmetric", "", "fail");
            return;
        }

        $targetItemStatements = $targetItem->getStatements();
        $targetItemStatementsArray = $targetItemStatements->toArray();

        $targetHasProperty = $this->hasProperty( $targetItemStatementsArray, $propertyId );
        $status = $targetHasProperty ? 'compliance' : 'violation';

        return new \CheckResult($propertyId, $dataValueString, "Symmetric", "", $status );
    }

    public function checkInverseConstraint( $propertyId, $dataValueString, $property) {
        $targetItem = $this->entityLookup->getEntity( new EntityId( $dataValueString->getSerialization() ));
        $parameterString = 'Property: ' . $property;
        if ($targetItem == null) {
            return new \CheckResult($propertyId, $dataValueString, "Inverse", §parameterString, "fail" );
            return;
        }
        $targetItemStatements = $targetItem->getStatements();
        $targetItemStatementsArray = $targetItemStatements->toArray();

        $targetHasProperty = $this->hasProperty( $targetItemStatementsArray, $property );
        $status = $targetHasProperty ? 'compliance' : 'violation';

        return new \CheckResult($propertyId, $dataValueString, "Inverse", §parameterString, $status );
    }

    // TODO
    public function checkConflictsWithConstraint( $propertyId, $dataValueString ) {
        return new \CheckResult($propertyId, $dataValueString, "Conflicts with", '\'\'(none)\'\'', "todo" );
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

    private function convertStringFromTemplatesToArray( $string ) {
        $toReplace = array("{", "}", "|", "[", "]", " ");
        return explode(",", str_replace($toReplace, "", $string));
    }

    private function entityFromParameter($getSerialization)
    {
    }
}