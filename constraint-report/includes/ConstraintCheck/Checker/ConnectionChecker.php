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

        if( $itemArray[0] === '' ) {
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
            $message = 'Properties with \'Conflicts with\' constraint need a parameter \'property\'.';
            return new CheckResult( $propertyId, $dataValue, 'Conflicts with', $parameters, 'violation', $message );
        }

        /*
         * 'Conflicts with' can be defined with
         *   a) a property only
         *   b) a property and a number of items (each combination of property and item forming an individual claim)
         */
        if( $itemArray[0] === '' ) {
            if( $this->hasProperty( $this->statements, $property ) ) {
                $message = 'This property must not be used when there is another statement using the property defined in the parameters.';
                $status = 'violation';
            } else {
                $message = '';
                $status = 'compliance';
            }
        } else {
            if( $this->hasClaim( $this->statements, $property, $itemArray ) ) {
                $message = 'This property must not be used when there is another statement using the property with one of the values defined in the parameters.';
                $status = 'violation';
            } else {
                $message = '';
                $status = 'compliance';
            }
        }

        return new CheckResult( $propertyId, $dataValue, 'Conflicts with', $parameters, $status, $message );
    }

    /**
     * Checks 'Item' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
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

        if( $itemArray[0] === '' ) {
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
            $message = 'Properties with \'Item\' constraint need a parameter \'property\'.';
            return new CheckResult( $propertyId, $dataValue, 'Item', $parameters, 'violation', $message );
        }

        /*
         * 'Item' can be defined with
         *   a) a property only
         *   b) a property and a number of items (each combination of property and item forming an individual claim)
         */
        if( $itemArray[0] === '' ) {
            if( $this->hasProperty( $this->statements, $property ) ) {
                $message = '';
                $status = 'compliance';
            } else {
                $message = 'This property must only be used when there is another statement using the property defined in the parameters.';
                $status = 'violation';
            }
        } else {
            if( $this->hasClaim( $this->statements, $property, $itemArray ) ) {
                $message = '';
                $status = 'compliance';
            } else {
                $message = 'This property must only be used when there is another statement using the property with one of the values defined in the parameters.';
                $status = 'violation';
            }
        }

        return new CheckResult( $propertyId, $dataValue, 'Item', $parameters, $status, $message );
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

        if( $itemArray[0] === '' ) {
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
        if( $dataValue->getType() !== 'wikibase-entityid' ) {
            $message = 'Properties with \'Target required claim\' constraint need to have values of type \'wikibase-entityid\'.';
            return new CheckResult( $propertyId, $dataValue, 'Target required claim', $parameters, 'violation', $message );
        }
        if( $property === null ) {
            $message = 'Properties with \'Target required claim\' constraint need a parameter \'property\'.';
            return new CheckResult( $propertyId, $dataValue, 'Target required claim', $parameters, 'violation', $message );
        }

        $targetItem = $this->entityLookup->getEntity( $dataValue->getEntityId() );
        if( $targetItem === null ) {
            $message = 'Target item does not exist.';
            return new CheckResult( $propertyId, $dataValue, 'Target required claim', $parameters, 'violation', $message );
        }
        $targetItemStatementsArray = $targetItem->getStatements()->toArray();

        /*
         * 'Target required claim' can be defined with
         *   a) a property only
         *   b) a property and a number of items (each combination forming an individual claim)
         */
        if( $itemArray[0] === '' ) {
            if( $this->hasProperty( $targetItemStatementsArray, $property ) ) {
                $message = '';
                $status = 'compliance';
            } else {
                $message = 'This property must only be used when there is a statement on its value entity using the property defined in the parameters.';
                $status = 'violation';
            }
        } else {
            if( $this->hasClaim( $targetItemStatementsArray, $property, $itemArray ) ) {
                $message = '';
                $status = 'compliance';
            } else {
                $message = 'This property must only be used when there is a statement on its value entity using the property with one of the values defined in the parameters.';
                $status = 'violation';
            }
        }

        return new CheckResult( $propertyId, $dataValue, 'Target required claim', $parameters, $status, $message );
    }

    /**
     * Checks 'Symmetric' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param string $entityIdSerialization
     * @return CheckResult
     */
    public function checkSymmetricConstraint( $propertyId, $dataValue, $entityIdSerialization ) {
        $parameters = array();

        /*
         * error handling:
         *   type of $dataValue for properties with 'Symmetric' constraint has to be 'wikibase-entityid'
         */
        if( $dataValue->getType() !== 'wikibase-entityid' ) {
            $message = 'Properties with \'Symmetric\' constraint need to have values of type \'wikibase-entityid\'.';
            return new CheckResult( $propertyId, $dataValue, 'Symmetric', $parameters, 'violation', $message );
        }

        $targetItem = $this->entityLookup->getEntity( $dataValue->getEntityId() );
        if( $targetItem === null ) {
            $message = 'Target item does not exist.';
            return new CheckResult( $propertyId, $dataValue, 'Symmetric', $parameters, 'violation', $message );
        }
        $targetItemStatementsArray = $targetItem->getStatements()->toArray();

        if( $this->hasClaim( $targetItemStatementsArray, $propertyId->getSerialization(), $entityIdSerialization ) ) {
            $message = '';
            $status = 'compliance';
        } else {
            $message = 'This property must only be used when there is a statement on its value entity with the same property and this item as its value.';
            $status = 'violation';
        }

        return new CheckResult( $propertyId, $dataValue, 'Symmetric', $parameters, $status, $message );
    }

    /**
     * Checks 'Inverse' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param string $entityIdSerialization
     * @param string $property
     * @return CheckResult
     */
    public function checkInverseConstraint( $propertyId, $dataValue, $entityIdSerialization, $property ) {
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
        if( $dataValue->getType() !== 'wikibase-entityid' ) {
            $message = 'Properties with \'Inverse\' constraint need to have values of type \'wikibase-entityid\'.';
            return new CheckResult( $propertyId, $dataValue, 'Inverse', $parameters, 'violation', $message );
        }
        if( $property === null ) {
            $message = 'Properties with \'Inverse\' constraint need a parameter \'property\'.';
            return new CheckResult( $propertyId, $dataValue, 'Inverse', $parameters, 'violation', $message );
        }

        $targetItem = $this->entityLookup->getEntity( $dataValue->getEntityId() );
        if( $targetItem === null ) {
            $message = 'Target item does not exist.';
            return new CheckResult( $propertyId, $dataValue, 'Inverse', $parameters, 'violation', $message );
        }
        $targetItemStatementsArray = $targetItem->getStatements()->toArray();

        if( $this->hasClaim( $targetItemStatementsArray, $propertyId->getSerialization(), $entityIdSerialization ) ) {
            $message = '';
            $status = 'compliance';
        } else {
            $message = 'This property must only be used when there is a statement on its value entity using the property defined in the parameters and this item as its value.';
            $status = 'violation';
        }

        return new CheckResult( $propertyId, $dataValue, 'Inverse', $parameters, $status, $message );
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