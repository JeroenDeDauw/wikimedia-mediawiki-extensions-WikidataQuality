<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\ItemId;

class TypeChecker {

    private $statements;
    private $entityLookup;

    public function __construct( $statements, $lookup) {
        $this->statements = $statements;
        $this->entityLookup = $lookup;
    }

    public function checkValueTypeConstraint( $propertyId, $dataValueString, $class, $classes, $relation ) {
        $status = null;
        if ( $class != null ) {
            $classesToCheck = array( $class );
        } else {
            $classesToCheck = explode(',', str_replace(' ', '', $classes) );
        }

        $relationId = $relation == 'instance' ? 31 : 279;
        $item = $this->entityLookup->getEntity( new ItemId( $dataValueString->getSerialization() ));
        if( !$item ) {
            return new CheckResult($propertyId, $dataValueString, "Type", implode(', ', $classesToCheck), 'fail' );
        }

        $statements = $this->entityLookup->getEntity( new ItemId( $dataValueString->getSerialization() ) )->getStatements();

        $status = $this->hasClassInRelation( $statements, $relationId, $classesToCheck );
        $status = $status ? 'compliance' : 'violation';
        return new CheckResult($propertyId, $dataValueString, "Type", implode(', ', $classesToCheck), $status );
    }

    public function checkTypeConstraint( $propertyId, $dataValueString, $statements, $class, $classes, $relation ) {
        $status = null;
        if ( $class != null ) {
            $classesToCheck = array( $class );
        } else {
            $classesToCheck = explode(',', $classes);
        }

        $relationId = $relation == 'instance' ? 31 : 279;

        $status = $this->hasClassInRelation( $statements, $relationId, $classesToCheck );
        $status = $status ? 'compliance' : 'violation';
        return new CheckResult($propertyId, $dataValueString, "Type", implode(', ', $classesToCheck), $status );
    }

    private function isSubclassOf($itemId, $classesToCheck) {
        $item = $this->entityLookup->getEntity( $itemId );
        if( !$item )
            return; //lookup failed, probably because item doesn't exist

        foreach( $item->getStatements() as $statement) {
            $claim = $statement->getClaim();
            if( $claim->getPropertyId()->getNumericId() == 279) {
                $mainSnak = $claim->getMainSnak();
                if( $mainSnak->getType() == 'value' ) {
                    $dataValueCompareString = $this->dataValueToString( $mainSnak->getDataValue() );
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
                    $dataValueCompareString = $this->dataValueToString( $mainSnak->getDataValue() );
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

    private function dataValueToString($dataValue)
    {
        $dataValueType = $dataValue->getType();
        switch( $dataValueType ) {
            case 'string':
            case 'decimal':
            case 'number':
            case 'boolean':
            case 'unknown':
                return $dataValue->getValue();
            case 'quantity':
                return $dataValue->getAmount()->getValue();
            case 'time':
                return $dataValue->getTime();
            case 'globecoordinate':
            case 'geocoordinate':
                return 'Latitude: ' . $dataValue->getLatitude() . ', Longitude: ' . $dataValue->getLongitude();
            case 'monolingualtext':
                return $dataValue->getText();
            case 'multilingualtext':
                return array_key_exists('en', $dataValue) ? $dataValue->getTexts()['en'] : array_shift($dataValue->getTexts());;
            case 'wikibase-entityid':
                return $dataValue->getEntityId();
            case 'bad':
            default:
                return null;
            //error case
        }
    }
}