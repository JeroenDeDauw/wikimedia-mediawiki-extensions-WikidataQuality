<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Class ConnectionChecker.
 * Checks 'Conflicts with', 'Item', 'Target required claim', 'Symmetric' and 'Inverse' constraints.
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Checker
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class ConnectionChecker {

    /**
     * List of all statemtens of given entity.
     * @var StatementList
     */
    private $statements;

    /**
     * Wikibase entity lookup.
     * @var \Wikibase\Lib\Store\EntityLookup
     */
    private $entityLookup;

    /**
     * Class for helper functions for constraint checkers.
     * @var ConstraintReportHelper
     */
    private $helper;

    /**
     * @param StatementList $statements
     * @param \Wikibase\Lib\Store\EntityLookup $lookup
     * @param ConstraintReportHelper $helper
     */
    public function __construct( $statements, $lookup, $helper  ) {
        $this->statements = $statements;
        $this->entityLookup = $lookup;
        $this->helper = $helper;
    }

    /**
     * Checks 'Conflicts with' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param string $property
     * @param array $itemArray
     * @return CheckResult
     */
    public function checkConflictsWithConstraint( $propertyId, $dataValue, $property, $itemArray ) {
        $parameters = array();

        if( $property === null ) {
            $parameters['property'] = array( 'null' );
        } else {
            $parameters['property'] = array( new PropertyId( $property ) );
        }

        if( empty( $itemArray ) ) {
            $parameters['item'] = array( 'null' );
        } else {
            $func = function( $item ) {
                if( $item !== 'novalue' && $item !== 'somevalue' && $item !== '' ) {
                    return new ItemId( $item );
                } else {
                    return $item;
                }
            };
            $parameters['item'] = array_map( $func, $itemArray );
        }

        /*
         * error handling:
         *   parameter $property must not be null
         */
        if( $property === null ) {
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

    /**
     * Checks 'Item' constraint.
     * @param PropertyId $propertyId
     * @param DataVaule $dataValue
     * @param string $property
     * @param array $itemArray
     * @return CheckResult
     */
    public function checkItemConstraint( $propertyId, $dataValue, $property, $itemArray ) {
        $parameters = array();

        if( $property === null ) {
            $parameters['property'] = array( 'null' );
        } else {
            $parameters['property'] = array( new PropertyId( $property ) );
        }

        if( empty( $itemArray ) ) {
            $parameters['item'] = array( 'null' );
        } else {
            $func = function( $item ) {
                if( $item !== 'novalue' && $item !== 'somevalue' && $item !== '' ) {
                    return new ItemId( $item );
                } else {
                    return $item;
                }
            };
            $parameters['item'] = array_map( $func, $itemArray );
        }

        /*
         * error handling:
         *   parameter $property must not be null
         */
        if( $property === null ) {
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

    /**
     * Checks 'Target required claim' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param string $property
     * @param array $itemArray
     * @return CheckResult
     */
    public function checkTargetRequiredClaimConstraint( $propertyId, $dataValue, $property, $itemArray ) {
        $parameters = array();

        if( $property === null ) {
            $parameters['property'] = array( 'null' );
        } else {
            $parameters['property'] = array( new PropertyId( $property ) );
        }

        if( empty( $itemArray ) ) {
            $parameters['item'] = array( 'null' );
        } else {
            $func = function( $item ) {
                if( $item !== 'novalue' && $item !== 'somevalue' && $item !== '' ) {
                    return new ItemId( $item );
                } else {
                    return $item;
                }
            };
            $parameters['item'] = array_map( $func, $itemArray );
        }

        /*
         * error handling:
         *   type of $dataValue for properties with 'Target required claim' constraint has to be 'wikibase-entityid'
         *   parameter $property must not be null
         */
        if( $dataValue->getType() !== 'wikibase-entityid' || $property === null ) {
            return new CheckResult( $propertyId, $dataValue, 'Target required claim', $parameters, 'error' );
        }

        $targetItem = $this->entityLookup->getEntity( $dataValue->getEntityId() );
        if( $targetItem === null ) {
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

    /**
     * Checks 'Symmetric' constraint.
     * @param PropertyId $propertyId
     * @param DataVaule $dataValue
     * @return CheckResult
     */
    public function checkSymmetricConstraint( $propertyId, $dataValue ) {
        $parameters = array();

        /*
         * error handling:
         *   type of $dataValue for properties with 'Symmetric' constraint has to be 'wikibase-entityid'
         */
        if( $dataValue->getType() !== 'wikibase-entityid' ) {
            return new CheckResult( $propertyId, $dataValue, 'Symmetric', $parameters, 'error' );
        }

        $targetItem = $this->entityLookup->getEntity( $dataValue->getEntityId() );
        if( $targetItem === null ) {
            return new CheckResult( $propertyId, $dataValue, 'Symmetric', $parameters, 'fail' );
        }
        $targetItemStatementsArray = $targetItem->getStatements()->toArray();

        $status = $this->hasProperty( $targetItemStatementsArray, $propertyId ) ? 'compliance' : 'violation';

        return new CheckResult( $propertyId, $dataValue, 'Symmetric', $parameters, $status );
    }

    /**
     * Checks 'Inverse' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param string $property
     * @return CheckResult
     */
    public function checkInverseConstraint( $propertyId, $dataValue, $property ) {
        $parameters = array();

        if( $property === null ) {
            $parameters['property'] = array( 'null' );
        } else {
            $parameters['property'] = array( new PropertyId( $property ) );
        }

        /*
         * error handling:
         *   type of $dataValue for properties with 'Inverse' constraint has to be 'wikibase-entityid'
         *   parameter $property must not be null
         */
        if( $dataValue->getType() !== 'wikibase-entityid' || $property === null ) {
            return new CheckResult( $propertyId, $dataValue, 'Inverse', $parameters, 'error' );
        }

        $targetItem = $this->entityLookup->getEntity( $dataValue->getEntityId() );
        if( $targetItem === null ) {
            return new CheckResult( $propertyId, $dataValue, 'Inverse', $parameters, 'fail' );
        }
        $targetItemStatementsArray = $targetItem->getStatements()->toArray();

        $status = $this->hasProperty( $targetItemStatementsArray, $property ) ? 'compliance' : 'violation';

        return new CheckResult( $propertyId, $dataValue, 'Inverse', $parameters, $status );
    }

    /**
     * Checks if there is a statement with a claim using the given property.
     * @param array $statementsArray
     * @param string $propertyIdSerialization
     * @return boolean
     */
    private function hasProperty( $statementsArray, $propertyIdSerialization ) {
        foreach( $statementsArray as $statement ) {
            if( $statement->getPropertyId()->getSerialization() === $propertyIdSerialization ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if there is a statement with a claim using the given property and having one of the given items as its value.
     * @param array $statementsArray
     * @param string $propertyIdSerialization
     * @param mixed string|array $itemIdSerializationOrArray
     * @return boolean
     */
    private function hasClaim( $statementsArray, $propertyIdSerialization, $itemIdSerializationOrArray ) {
        foreach( $statementsArray as $statement ) {
            if( $statement->getPropertyId()->getSerialization() === $propertyIdSerialization ) {
                if( is_string( $itemIdSerializationOrArray ) ) { // string
                    $itemIdSerializationArray = array( $itemIdSerializationOrArray );
                } else { // array
                    $itemIdSerializationArray = $itemIdSerializationOrArray;
                }
                return $this->arrayHasClaim( $statement, $itemIdSerializationArray );
            }
        }
        return false;
    }

    private function arrayHasClaim( $statement, $itemIdSerializationArray ) {
        foreach( $itemIdSerializationArray as $itemIdSerialization ) {
            $mainSnak = $statement->getMainSnak();
            if( $mainSnak->getType() === 'value' ) {
                if( $mainSnak->getDataValue() === 'wikibase-entityid' ) {
                    if( $mainSnak->getDataValue()->getEntityId()->getSerialization() === $itemIdSerialization ) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

}