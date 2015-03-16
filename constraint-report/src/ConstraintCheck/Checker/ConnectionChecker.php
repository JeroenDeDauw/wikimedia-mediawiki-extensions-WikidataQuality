<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

class ConnectionChecker {

    private $statements;
    private $entityLookup;
    private $helper;

    public function __construct( $statements, $lookup, $helper  ) {
        $this->statements = $statements;
        $this->entityLookup = $lookup;
        $this->helper = $helper;
    }

    public function checkConflictsWithConstraint( $propertyId, $dataValue, $property, $itemArray ) {
        $parameters = array();

        if( $property == null ) {
            $parameters['property'] = array( 'null' );
        } else {
            $parameters['property'] = array( new PropertyId( $property ) );
        }

        if( empty( $itemArray ) ) {
            $parameters['item'] = array( 'null' );
        } else {
            $func = function( $item ) {
                return new ItemId( $item );
            };
            $parameters['item'] = array_map( $func, $itemArray );
        }

        /*
         * error handling:
         *   parameter $property must not be null
         */
        if( $property == null ) {
            return new CheckResult( $propertyId, $dataValue, 'Conflicts with', $parameters, 'error' );
        }

        /*
         * 'Conflicts with' can be defined with
         *   a) a property only
         *   b) a property and a number of items (each combination of property and item forming an individual claim)
         */
        if( empty( $itemArray ) ) {
            $status = $this->hasProperty( $this->statements, $property ) ? 'violation' : 'compliance';
        } else {
            $status = $this->hasClaim( $this->statements, $property, $itemArray ) ? 'violation' : 'compliance';
        }

        return new CheckResult( $propertyId, $dataValue, 'Conflicts with', $parameters, $status );
    }

    public function checkItemConstraint( $propertyId, $dataValue, $property, $itemArray ) {
        $parameters = array();

        if( $property == null ) {
            $parameters['property'] = array( 'null' );
        } else {
            $parameters['property'] = array( new PropertyId( $property ) );
        }

        if( empty( $itemArray ) ) {
            $parameters['item'] = array( 'null' );
        } else {
            $func = function( $item ) {
                return new ItemId( $item );
            };
            $parameters['item'] = array_map( $func, $itemArray );
        }

        /*
         * error handling:
         *   parameter $property must not be null
         */
        if( $property == null ) {
            return new CheckResult( $propertyId, $dataValue, 'Item', $parameters, 'error' );
        }

        /*
         * 'Item' can be defined with
         *   a) a property only
         *   b) a property and a number of items (each combination of property and item forming an individual claim)
         */
        if( empty( $itemArray ) ) {
            $status = $this->hasProperty( $this->statements, $property ) ? 'compliance' : 'violation';
        } else {
            $status = $this->hasClaim( $this->statements, $property, $itemArray ) ? 'compliance' : 'violation';
        }

        return new CheckResult( $propertyId, $dataValue, 'Item', $parameters, $status );
    }

    public function checkTargetRequiredClaimConstraint( $propertyId, $dataValue, $property, $itemArray ) {
        $parameters = array();

        if( $property == null ) {
            $parameters['property'] = array( 'null' );
        } else {
            $parameters['property'] = array( new PropertyId( $property ) );
        }

        if( empty( $itemArray ) ) {
            $parameters['item'] = array( 'null' );
        } else {
            $func = function( $item ) {
                return new ItemId( $item );
            };
            $parameters['item'] = array_map( $func, $itemArray );
        }

        /*
         * error handling:
         *   type of $dataValue for properties with 'Target required claim' constraint has to be 'wikibase-entityid'
         *   parameter $property must not be null
         */
        if( $dataValue->getType() != 'wikibase-entityid' || $property == null ) {
            return new CheckResult( $propertyId, $dataValue, 'Target required claim', $parameters, 'error' );
        }

        $targetItem = $this->entityLookup->getEntity( $dataValue->getEntityId() );
        if( $targetItem == null ) {
            return new CheckResult( $propertyId, $dataValue, 'Target required claim', $parameters, 'fail' );
        }
        $targetItemStatementsArray = $targetItem->getStatements()->toArray();

        /*
         * 'Target required claim' can be defined with
         *   a) a property only
         *   b) a property and a number of items (each combination forming an individual claim)
         */
        if( empty( $itemArray ) ) {
            $status = $this->hasProperty( $targetItemStatementsArray, $property ) ? 'compliance' : 'violation';
        } else {
            $status = $this->hasClaim( $targetItemStatementsArray, $property, $itemArray ) ? 'compliance' : 'violation';
        }

        return new CheckResult( $propertyId, $dataValue, 'Target required claim', $parameters, $status );
    }

    public function checkSymmetricConstraint( $propertyId, $dataValue ) {
        $parameters = array();

        /*
         * error handling:
         *   type of $dataValue for properties with 'Symmetric' constraint has to be 'wikibase-entityid'
         */
        if( $dataValue->getType() != 'wikibase-entityid' ) {
            return new CheckResult( $propertyId, $dataValue, 'Symmetric', $parameters, 'error' );
        }

        $targetItem = $this->entityLookup->getEntity( $dataValue->getEntityId() );
        if( $targetItem == null ) {
            return new CheckResult( $propertyId, $dataValue, 'Symmetric', $parameters, 'fail' );
        }
        $targetItemStatementsArray = $targetItem->getStatements()->toArray();

        $status = $this->hasProperty( $targetItemStatementsArray, $propertyId ) ? 'compliance' : 'violation';

        return new CheckResult( $propertyId, $dataValue, 'Symmetric', $parameters, $status );
    }

    public function checkInverseConstraint( $propertyId, $dataValue, $property ) {
        $parameters = array();

        if( $property == null ) {
            $parameters['property'] = array( 'null' );
        } else {
            $parameters['property'] = array( new PropertyId( $property ) );
        }

        /*
         * error handling:
         *   type of $dataValue for properties with 'Inverse' constraint has to be 'wikibase-entityid'
         *   parameter $property must not be null
         */
        if( $dataValue->getType() != 'wikibase-entityid' || $property == null ) {
            return new CheckResult( $propertyId, $dataValue, 'Inverse', $parameters, 'error' );
        }

        $targetItem = $this->entityLookup->getEntity( $dataValue->getEntityId() );
        if( $targetItem == null ) {
            return new CheckResult( $propertyId, $dataValue, 'Inverse', $parameters, 'fail' );
        }
        $targetItemStatementsArray = $targetItem->getStatements()->toArray();

        $status = $this->hasProperty( $targetItemStatementsArray, $property ) ? 'compliance' : 'violation';

        return new CheckResult( $propertyId, $dataValue, 'Inverse', $parameters, $status );
    }

    private function hasProperty( $itemStatementsArray, $propertyId ) {
        foreach( $itemStatementsArray as $itemStatement ) {
            if( $itemStatement->getPropertyId()->getSerialization() == $propertyId ) {
                return true;
            }
        }
        return false;
    }

    private function hasClaim( $statementsArray, $propertyId, $itemIdOrArray ) {
        foreach( $statementsArray as $statement ) {
            if( $statement->getPropertyId()->getSerialization() == $propertyId ) {
                if( is_string( $itemIdOrArray ) ) {
                    if( $this->singleHasClaim( $statement, $itemIdOrArray ) ) {
                        return true;
                    }
                } else {
                    if( $this->arrayHasClaim( $statement, $itemIdOrArray ) ) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function singleHasClaim( $statement, $itemId ) {
        if( $statement->getMainSnak()->getDataValue()->getEntityId()->getSerialization() == $itemId ) {
            return true;
        } else {
            return false;
        }
    }

    private function arrayHasClaim( $statement, $itemIdArray ) {
        foreach( $itemIdArray as $itemId ) {
            if( $statement->getMainSnak()->getDataValue()->getEntityId()->getSerialization() == $itemId ) {
                return true;
            }
        }
        return false;
    }

}