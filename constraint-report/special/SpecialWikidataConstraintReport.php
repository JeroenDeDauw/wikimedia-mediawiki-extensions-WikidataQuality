<?php

use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Repo\Store;
use Wikibase\DataModel\Statement;
use Wikibase\DataModel\Snak;

class SpecialWikidataConstraintReport extends SpecialPage {
	function __construct() {
		parent::__construct( 'WikidataConstraintReport' );
	}
 
	function execute( $par ) {
		global $wgRequest, $wgOut;
		$this->setHeaders();

		$out = $this->getContext()->getOutput();

		$lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();
		
		$entity = entityFromPar($par[0]);
		if ($entity == -1) {
			$out->addWikiText("No valid entityID given. Usage: .../Q42 or .../P42");
			exit(1);
		}
		
		$entityStatements = $entity->getStatements();

		$dbr = wfGetDB( DB_SLAVE );

		foreach( $entityStatements as $statement ) {

			$claim = $statement->getClaim();

			$propertyId = $claim->getPropertyId();
			$numericPropertyId = $propertyId->getNumericId();

			$dataValue= $claim->getMainSnak()->getDataValue();

			$res = $dbr->select(
				'wbq_constraints_from_templates',						// $table
				array( 'pid', 'constraint_name', 'min', 'max' ),		// $vars (columns of the table)
				("pid = $numericPropertyId"),							// $conds
				__METHOD__,												// $fname = 'Database::select',
				array( '' )												// $options = array()
			);

			foreach( $res as $row ) {

				switch( $row->constraint_name ) {
					case 'Single value':
						checkSingleValueConstraint( $propertyId, $dataValue );
						break;
					case 'Range':
						checkRangeConstraint( $propertyId, $dataValue, $row->min, $row->max );
						break;
					case 'Symmetric':
						checkSymmetricConstraint( $propertyId, $dataValue );
						break;
					default:
						//not yet implemented cases, also error case
						break;
				}

			}

		}

		function entityFromPar($parameter){
			switch(strtoupper($par[0])) {
			case 'Q':
				return $lookup->getEntity(new ItemId($par));
			case 'P':
				return $lookup->getEntity(new PropertyId($par));
			default:
				return -1;
			}
		}
		
		function checkSingleValueConstraint( $propertyId, $dataValue ) {
			//todo
			/*
			 * might be harder to check than thought
			 * how are several values per property per entity stored?
			 * 	- several statements, each of which with the same property?
			 * 	- several dataValues, and if so, how to access them?
			 */
		}

		function checkRangeConstraint( $propertyId ,$dataValue, $min, $max ) {
			//todo
			/*
			 * cast min and max to int? why are they stored as varchar(255) anyway?
			 * what dataValue is it? decimalValue, numberValue, quantityValue?
			 */
			global $out;
			$output = '';

			$dataValueType = $dataValue->getValue()->getType();
			switch( $dataValueType ) {
				case 'decimal':
					$value = $dataValue->getValue();
					break;
				case 'number':
					$value = $dataValue->getValue();
					break;
				case 'quantity':
					$value = $dataValue->getAmount()->getValue();
					break;
				default:
					//error case
			}
			
			if( $value < $min || $value > $max ) {
				$output .= "\'\'VIOLATION:\'\' The claim {{Property:$propertyId}}: $value violates the {{tl|Range Constraint}}: min $min, max $max\n\n";
			} else {
				$output .= "The claim {{Property:$propertyId}}: $value complies with the {{tl|Range Constraint}}: min $min, max $max\n\n";
			}
			$out->addWikiText($output);
		}

		function checkSymmetricConstraint( $propertyId ,$dataValue ) {
			//todo
		}


		
		/*
		$output = '';

		$dbr = wfGetDB( DB_SLAVE );
		
		$res = $dbr->select(
			'page',									// $table
			array( 'page_title' ),					// $vars (columns of the table)
			'page_namespace = 122',					// $conds
			__METHOD__,								// $fname = 'Database::select',
			array( 'ORDER BY' => 'page_title ASC' )	// $options = array()
		);
		$output = '';
		
		foreach( $res as $row ) {
			$output .= '[[Property:' . $row->page_title . "]]\n\n";
		}
 
		$wgOut->addWikiText( $output );
		*/
		
	}
}