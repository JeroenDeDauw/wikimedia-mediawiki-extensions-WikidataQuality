<?php

class TypeChecker {

    private $statements;

    public function __construct($statements)
    {
        $this->statements = $statements;
    }


    public function checkValueTypeConstraint( $propertyId, $dataValueString, $class, $classes, $relation ) {
        $status = null;
        if ( $class != null ) {
            $classesToCheck = array( $class );
        } else {
            $classesToCheck = explode(',', str_replace(' ', '', $classes) );
        }

        $relationId = $relation == 'instance' ? 31 : 279;
        $item = $this->entityFromParameter( $dataValueString->getSerialization() );
        if( !$item ) {
            return new \CheckResult($propertyId, $dataValueString, "Type", implode(', ', $classesToCheck), 'fail' );
            return;
        }

        $statements = $this->entityFromParameter( $dataValueString->getSerialization() )->getStatements();

        $status = $this->hasClassInRelation( $statements, $relationId, $classesToCheck );
        $status = $status ? 'compliance' : 'violation';
        return new \CheckResult($propertyId, $dataValueString, "Type", implode(', ', $classesToCheck), $status );
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
        return new \CheckResult($propertyId, $dataValueString, "Type", implode(', ', $classesToCheck), $status );
    }

    private function isSubclassOf($itemId, $classesToCheck) {
        $item = $this->entityFromParameter( $itemId->getSerialization() );
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
}