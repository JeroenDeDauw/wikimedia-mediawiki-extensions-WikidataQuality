<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\ItemId;
use Exception;

/**
 * Class TypeChecker
 * Checks Type and Value type constraint.
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Checker
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class TypeChecker {

    private $entityLookup;
    private $helper;

    const instanceId = 31;
    const subclassId = 279;

    public function __construct( $lookup, $helper ) {
        $this->entityLookup = $lookup;
        $this->helper = $helper;
    }

    public function checkValueTypeConstraint( $propertyId, $dataValue, $classArray, $relation ) {
        $parameters = array();

        if( empty( $classArray ) ) {
            $parameters['class'] = array( 'null' );
        } else {
            $func = function( $class ) {
                return new ItemId( $class );
            };
            $parameters['item'] = array_map( $func, $classArray );
        }

        if( $relation == null ) {
            $parameters['relation'] = array( 'null' );
        } else {
            $parameters['relation'] = array( $relation );
        }

        /*
         * error handling:
         *   type of $dataValue for properties with 'Value type' constraint has to be 'wikibase-entityid'
         *   parameter $classArray must not be null
         */
        if( $dataValue->getType() != 'wikibase-entityid' || $classArray == null ) {
            return new CheckResult( $propertyId, $dataValue, 'Value type', $parameters, 'error' );
        }

        /*
         * error handling:
         *   parameter $relation must be either 'instance' or 'subclass'
         */
        if( $relation == 'instance' ) {
            $relationId = self::instanceId;
        } else if( $relation == 'subclass' ) {
            $relationId = self::subclassId;
        } else {
            return new CheckResult( $propertyId, $dataValue, 'Value type', $parameters, 'error' );
        }

        try {
            $item = $this->entityLookup->getEntity( $dataValue->getEntityId() );
        } catch( Exception $ex ) {
            return new CheckResult( $propertyId, $dataValue, 'Value type', $parameters, 'error' );
        }
        if( !$item ) {
            return new CheckResult( $propertyId, $dataValue, 'Value type', $parameters, 'fail' );
        }

        $statements = $this->entityLookup->getEntity( $dataValue->getEntityId() )->getStatements();

        $status = $this->hasClassInRelation( $statements, $relationId, $classArray );
        $status = $status ? 'compliance' : 'violation';
        return new CheckResult( $propertyId, $dataValue, 'Value type', $parameters, $status );
    }

    public function checkTypeConstraint( $propertyId, $dataValue, $statements, $classArray, $relation ) {
        $parameters = array();

        if( empty( $classArray ) ) {
            $parameters['class'] = array( 'null' );
        } else {
            $func = function( $class ) {
                return new ItemId( $class );
            };
            $parameters['item'] = array_map( $func, $classArray );
        }

        if( $relation == null ) {
            $parameters['relation'] = array( 'null' );
        } else {
            $parameters['relation'] = array( $relation );
        }

        /*
         * error handling:
         *   parameter $classArray must not be null
         */
        if ( $classArray == null ) {
            return new CheckResult( $propertyId, $dataValue, 'Type', $parameters, 'error' );
        }

        /*
         * error handling:
         *   parameter $relation must be either 'instance' or 'subclass'
         */
        if( $relation == 'instance' ) {
            $relationId = self::instanceId;
        } else if( $relation == 'subclass' ) {
            $relationId = self::subclassId;
        } else {
            return new CheckResult( $propertyId, $dataValue, 'Type', $parameters, 'error' );
        }

        $status = $this->hasClassInRelation( $statements, $relationId, $classArray );
        $status = $status ? 'compliance' : 'violation';
        return new CheckResult( $propertyId, $dataValue, 'Type', $parameters, $status );
    }

    private function isSubclassOf( $comparativeClass, $classesToCheck ) {
        $item = $this->entityLookup->getEntity( $comparativeClass );
        if( !$item ) {
            return false; // lookup failed, probably because item doesn't exist
        }

        foreach( $item->getStatements() as $statement ) {
            $claim = $statement->getClaim();
            $propertyId = $claim->getPropertyId();
            $numericPropertyId = $propertyId->getNumericId();

            if( $numericPropertyId == self::subclassId ) {
                $mainSnak = $claim->getMainSnak();

                if( $mainSnak->getType() == 'value' && $mainSnak->getDataValue()->getType() == 'wikibase-entityid' ) {
                    $comparativeClass = $mainSnak->getDataValue()->getEntityId();
                } else {
                    // error case
                }

                foreach( $classesToCheck as $class ) {
                    if( $class == $comparativeClass->getSerialization() ) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function hasClassInRelation( $statements, $relationId, $classesToCheck ) {
        $compliance = null;
        foreach( $statements as $statement ) {
            $claim = $statement->getClaim();
            $propertyId = $claim->getPropertyId();
            $numericPropertyId = $propertyId->getNumericId();

            if( $numericPropertyId == $relationId ) {
                $mainSnak = $claim->getMainSnak();

                if( $mainSnak->getType() == 'value' && $mainSnak->getDataValue()->getType() == 'wikibase-entityid' ) {
                    $comparativeClass = $mainSnak->getDataValue()->getEntityId();
                } else {
                    // error case
                }

                foreach( $classesToCheck as $class ) {
                    if( $class == $comparativeClass->getSerialization() ) {
                        return true;
                    }
                }

                $compliance = $this->isSubclassOf( $comparativeClass, $classesToCheck );
            }
            if( $compliance == true ) {
                return true;
            }
        }
    }

}