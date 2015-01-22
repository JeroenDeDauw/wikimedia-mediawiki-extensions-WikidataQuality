<?php

use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Repo\Store;
use Wikibase\DataModel\Statement;
use Wikibase\DataModel\Snak;

class SpecialWikidataConstraint extends SpecialPage {
	function __construct() {
		parent::__construct( 'WikidataConstraint' );
	}
 
	function execute( $par ) {
		global $wgRequest;
		$this->setHeaders();

		$out = $this->getContext()->getOutput();

		$out->addWikiMsg( 'wikidataconstraint-summary' );
        $out->addHTML( '<p>Just enter an entity you want to be checked against the constraint templates.<br/>'
            . 'Try for example <i>Qxx</i> (John Lennon) and <i>Qxx</i> (Imagine)'
            . ' and look at the results.</p>'
        );
        $out->addHTML( "<form name='ItemIdForm' action='" . $_SERVER['PHP_SELF'] . "' method='post'>" );
        $out->addHTML( "<input placeholder='Qxx' name='itemId' id='item-input'>" );
        $out->addHTML( "<input type='submit' value='Cross-check' id='check-item-btn' />" );
        $out->addHTML( "</form>" );
        /*$out->addHTML( "<p/>" );
        $out->addHTML( "<ul id='results-list'></ul>" );
        $out->addHTML( "<p/>" );
        $out->addHTML( "<div id='result'></div>" );*/

        if (isset($_POST['itemId'])) {
            $id = new ItemId( $_POST['itemId'] );
            $lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();
            $entity = $lookup->getEntity($id);
            doStuff()
        }

		

		function doStuff() {
			$entityStatements = $entity->getStatements();

			$dbr = wfGetDB( DB_SLAVE );

			foreach( $entityStatements as $statement ) {

				$claim = $statement->getClaim();

				$propertyId = $claim->getPropertyId();
				$numericPropertyId = $propertyId->getNumericId();

				$dataValue= $claim->getMainSnak()->getDataValue();

				$res = $dbr->select(
					'constraints_from_templates',							// $table
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
			$numericId = $propertyId->getNumericId();
			if( $value < $min || $value > $max ) {
				$output .= "\'\'VIOLATION:\'\' The claim {{Property:P$numericId}}: $value violates the {{tl|Range Constraint}}: min $min, max $max\n\n";
			} else {
				$output .= "The claim {{Property:P$numericId}}: $value complies with the {{tl|Range Constraint}}: min $min, max $max\n\n";
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