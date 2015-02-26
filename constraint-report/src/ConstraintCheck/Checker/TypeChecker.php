<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\DataValueParser;
use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\ItemId;
use \Exception;

class TypeChecker {

    private $entityLookup;
    private $helper;

    public function __construct( $lookup, $helper ) {
        $this->entityLookup = $lookup;
        $this->helper = $helper;
    }

    public function checkValueTypeConstraint( $propertyId, $dataValueString, $classArray, $relation ) {
        $status = null;

        $relationId = $relation == 'instance' ? 31 : 279;

        $parameterString = $this->helper->limitOutput( 'class: ' . $this->helper->arrayToString( $classArray ) . ', relation: ' . $relation );

        try {
            $item = $this->entityLookup->getEntity( new ItemId( $dataValueString ) );
        } catch( Exception $ex ) {
            return new CheckResult( $propertyId, $dataValueString, "Value type", $parameterString, 'error' );
        }
        if( !$item ) {
            return new CheckResult( $propertyId, $dataValueString, "Value type", $parameterString, 'fail' );
        }

        $statements = $this->entityLookup->getEntity( new ItemId( $dataValueString ) )->getStatements();

        $status = $this->hasClassInRelation( $statements, $relationId, $classArray );
        $status = $status ? 'compliance' : 'violation';
        return new CheckResult( $propertyId, $dataValueString, "Value type", $parameterString, $status );
    }

    public function checkTypeConstraint( $propertyId, $dataValueString, $statements, $classArray, $relation ) {
        $status = null;

        $relationId = $relation == 'instance' ? 31 : 279;

        $parameterString = $this->helper->limitOutput( 'class: ' . $this->helper->arrayToString( $classArray ) . ', relation: ' . $relation );

        $status = $this->hasClassInRelation( $statements, $relationId, $classArray );
        $status = $status ? 'compliance' : 'violation';
        return new CheckResult($propertyId, $dataValueString, "Type", $parameterString, $status );
    }

    private function isSubclassOf( $itemIdString, $classesToCheck ) {
        $item = $this->entityLookup->getEntity( new ItemId( $itemIdString ) );
        if( !$item )
            return; //lookup failed, probably because item doesn't exist

        foreach( $item->getStatements() as $statement) {
            $claim = $statement->getClaim();
            if( $claim->getPropertyId()->getNumericId() == 279) {
                $mainSnak = $claim->getMainSnak();
                if( $mainSnak->getType() == 'value' ) {
                    $dataValueCompareString = $this->helper->dataValueToString( $mainSnak->getDataValue() );
                } else {
                    $dataValueCompareString = '\'\'(' . $mainSnak->getType() . '\'\')';
                }

                foreach( $classesToCheck as $val ) {
                    if( $val == $dataValueCompareString) {
                        return 'compliance';
                    }
                }
            }
        }
        return;
    }

    private function hasClassInRelation( $statements, $relationId, $classesToCheck ) {
        foreach( $statements as $statement ) {
            $claim = $statement->getClaim();
            $propertyId = $claim->getPropertyId();
            $numericPropertyId = $propertyId->getNumericId();

            if( $numericPropertyId == $relationId ){
                $mainSnak = $claim->getMainSnak();
                if( $mainSnak->getType() == 'value' ) {
                    $dataValueCompareString = $this->helper->dataValueToString( $mainSnak->getDataValue() );
                } else {
                    $dataValueCompareString = '\'\'(' . $mainSnak->getType() . '\'\')';
                }

                foreach( $classesToCheck as $val ) {
                    if( $val == $dataValueCompareString) {
                        return 'compliance';
                    }
                }

                return $this->isSubclassOf($dataValueCompareString, $classesToCheck);
            }
        }
    }

}