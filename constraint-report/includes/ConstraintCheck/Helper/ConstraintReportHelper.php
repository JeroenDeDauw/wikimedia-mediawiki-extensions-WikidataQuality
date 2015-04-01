<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Helper;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Class ConstraintReportHelper
 * Class for helper functions for constraint checkers.
 * @package WikidataQuality\ConstraintReport\ConstraintCheck\Helper
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class ConstraintReportHelper {

    /**
     * @param string $templateString
     * @return string
     */
    public function removeBrackets( $templateString ) {
        $toReplace = array( '{', '}', '|', '[', ']' );
        return str_replace( $toReplace, '', $templateString );
    }

    /**
     * Used to convert a string containing a comma-separated list (as one gets out of the constraints table) to an array.
     * @param string $templateString
     * @return array
     */
    public function stringToArray( $templateString ) {
        if( is_null( $templateString ) or $templateString === '' ) {
            return array( '' );
        } else {
            return explode( ',', $this->removeBrackets( str_replace( ' ', '', $templateString) ) );
        }
    }

    /**
     * Helps set the item/class/property parameter according to what is given in the database.
     * @param array $entityArray
     * @param string $type
     * @return array
     */
    public function parseParameterArray( $entityArray, $type ) {
        $itemAndClassFunc = function( $entity ) {
            if( $entity !== 'novalue' && $entity !== 'somevalue' && $entity !== '' ) { // exclude special cases
                return new ItemId( $entity );
            } else {
                return $entity;
            }
        };

        $propertyFunc = function( $entity ) {
            if( $entity !== '' ) { // exclude special cases
                return new PropertyId( $entity );
            } else {
                return $entity;
            }
        };

        if( $entityArray[0] === '' ) { // parameter not given
            return array( 'null' );
        } else {
            if( $type === 'Item' || $type === 'Class' ) {
                return array_map( $itemAndClassFunc, $entityArray );
            } else if( $type = 'Property' ) {
                return array_map( $propertyFunc, $entityArray );
            }
        }
    }

}