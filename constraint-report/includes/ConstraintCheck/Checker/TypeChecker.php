<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\ItemId;
use Exception;

/**
 * Class TypeChecker.
 * Checks 'Type' and 'Value type' constraint.
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Checker
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class TypeChecker {

    /**
     * Class for helper functions for constraint checkers.
     * @var ConstraintReportHelper
     */
    private $helper;

    const instanceId = 31;
    const subclassId = 279;

    /**
     * @param StatementList $statements
     * @param ConstraintReportHelper $helper
     */
    public function __construct( $lookup, $helper ) {
        $this->entityLookup = $lookup;
        $this->helper = $helper;
    }

    /**
     * Checks 'Value type' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param array $classArray
     * @param string $relation
     * @return CheckResult
     */
    public function checkValueTypeConstraint( $propertyId, $dataValue, $classArray, $relation ) {
        $parameters = array();

        $parameters['class'] = $this->helper->parseParameterArray( $classArray, 'ItemId' );
        $parameters['relation'] = $this->helper->parseSingleParameter( $relation );

        /*
         * error handling:
         *   type of $dataValue for properties with 'Value type' constraint has to be 'wikibase-entityid'
         *   parameter $classArray must not be null
         */
        if( $dataValue->getType() !== 'wikibase-entityid' ) {
            $message = 'Properties with \'Value type\' constraint need to have values of type \'wikibase-entityid\'.';
            return new CheckResult( $propertyId, $dataValue, 'Value type', $parameters, 'violation', $message );
        }
        if( $classArray[0] === '' ) {
            $message = 'Properties with \'Value type\' constraint need the parameter \'class\'.';
            return new CheckResult( $propertyId, $dataValue, 'Value type', $parameters, 'violation', $message );
        }

        /*
         * error handling:
         *   parameter $relation must be either 'instance' or 'subclass'
         */
        if( $relation === 'instance' ) {
            $relationId = self::instanceId;
        } else if( $relation === 'subclass' ) {
            $relationId = self::subclassId;
        } else {
            $message = 'Parameter \'relation\' must be either \'instance\' or \'subclass\'.';
            return new CheckResult( $propertyId, $dataValue, 'Value type', $parameters, 'violation', $message );
        }

        try {
            $item = $this->entityLookup->getEntity( $dataValue->getEntityId() );
        } catch( Exception $ex ) {
            $message = 'Could not load this property\'s value entity.';
            return new CheckResult( $propertyId, $dataValue, 'Value type', $parameters, 'violation', $message );
        }
        if( !$item ) {
            $message = 'This property\'s value entity does not exist.';
            return new CheckResult( $propertyId, $dataValue, 'Value type', $parameters, 'violation', $message );
        }

        $statements = $this->entityLookup->getEntity( $dataValue->getEntityId() )->getStatements();

        if( $this->hasClassInRelation( $statements, $relationId, $classArray ) ) {
            $message = '';
            $status = 'compliance';
        } else {
            $message = 'This property\'s value entity must be in the relation to the item (or a subclass of the item) defined in the parameters.';
            $status = 'violation';
        }

        return new CheckResult( $propertyId, $dataValue, 'Value type', $parameters, $status, $message );
    }

    /**
     * Checks 'Value type' constraint.
     * @param PropertyId $propertyId
     * @param DataValue $dataValue
     * @param StatementList $statements
     * @param array $classArray
     * @param string $relation
     * @return CheckResult
     */
    public function checkTypeConstraint( $propertyId, $dataValue, $statements, $classArray, $relation ) {
        $parameters = array();

        $parameters['class'] = $this->helper->parseParameterArray( $classArray, 'ItemId' );
        $parameters['relation'] = $this->helper->parseSingleParameter( $relation );

        /*
         * error handling:
         *   parameter $classArray must not be null
         */
        if ( $classArray[0] === '' ) {
            $message = 'Properties with \'Type\' constraint need the parameter \'class\'.';
            return new CheckResult( $propertyId, $dataValue, 'Type', $parameters, 'violation', $message );
        }

        /*
         * error handling:
         *   parameter $relation must be either 'instance' or 'subclass'
         */
        if( $relation === 'instance' ) {
            $relationId = self::instanceId;
        } else if( $relation === 'subclass' ) {
            $relationId = self::subclassId;
        } else {
            $message = 'Parameter \'relation\' must be either \'instance\' or \'subclass\'.';
            return new CheckResult( $propertyId, $dataValue, 'Type', $parameters, 'violation', $message );
        }

        if( $this->hasClassInRelation( $statements, $relationId, $classArray ) ) {
            $message = '';
            $status = 'compliance';
        } else {
            $message = 'This property must only be used on items that are in the relation to the item (or a subclass of the item) defined in the parameters.';
            $status = 'violation';
        }

        return new CheckResult( $propertyId, $dataValue, 'Type', $parameters, $status, $message );
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

            if( $numericPropertyId === self::subclassId ) {
                $mainSnak = $claim->getMainSnak();

                if( $mainSnak->getType() === 'value' && $mainSnak->getDataValue()->getType() === 'wikibase-entityid' ) {
                    $comparativeClass = $mainSnak->getDataValue()->getEntityId();
                } else {
                    // error case
                }

                foreach( $classesToCheck as $class ) {
                    if( $class === $comparativeClass->getSerialization() ) {
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

            if( $numericPropertyId === $relationId ) {
                $mainSnak = $claim->getMainSnak();

                if( $mainSnak->getType() === 'value' && $mainSnak->getDataValue()->getType() === 'wikibase-entityid' ) {
                    $comparativeClass = $mainSnak->getDataValue()->getEntityId();
                } else {
                    // error case
                }

                foreach( $classesToCheck as $class ) {
                    if( $class === $comparativeClass->getSerialization() ) {
                        return true;
                    }
                }

                $compliance = $this->isSubclassOf( $comparativeClass, $classesToCheck );
            }
            if( $compliance === true ) {
                return true;
            }
        }
    }

}