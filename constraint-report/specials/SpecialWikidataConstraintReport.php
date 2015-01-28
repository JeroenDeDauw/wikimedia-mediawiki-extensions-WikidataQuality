<?php

namespace WikidataQuality\ConstraintReport\Specials;

use SpecialPage;
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
		parent::__construct( 'ConstraintReport' );
	}

	/**
	 * @see SpecialPage::getGroupName
	 *
	 * @return string
	 */
	function getGroupName() {
		return "wikidataquality";
	}

	/**
	 * @see SpecialPage::getDescription
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->msg( 'special-constraintreport' )->text();
	}


	/**
	 * @see SpecialPage::execute
	 *
	 * @param string|null $par
	 */
	function execute( $par ) {
		global $wgRequest, $wgOut;
		$this->setHeaders();

		$out = $this->getContext()->getOutput();

		// Show form
		$out->addHTML( '<p>Enter an item id or an property id and let it check against constraints.<br/>'
            . 'Try for example <i>Qxx</i> (XYZ) or <i>Pyy</i> (ABC)'
            . ' and look at the results.</p>'
        );
        $out->addHTML( "<form name='ItemIdForm' action='" . $_SERVER['PHP_SELF'] . "' method='post'>" );
        $out->addHTML( "<input placeholder='Qxx/Pxx' name='entityID' id='entity-input'>" );
		$out->addHTML( "<input type='submit' value='Check' />" );
		$out->addHTML( "</form><br/><br/>" );

		if (!isset($_POST['entityID'])) {
			//exit(0);
			return;
		}

		$lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();

		$entity = $this->entityFromPar($_POST['entityID']);
		if ($entity == -1) {
			$out->addWikiText("No valid entityID given. Usage: .../Q42 or .../P42\n\n");//todo
			return;
		}
		
		$entityStatements = $entity->getStatements();

		$dbr = wfGetDB( DB_SLAVE );

		foreach( $entityStatements as $statement ) {

			$claim = $statement->getClaim();

			$propertyId = $claim->getPropertyId();
			$numericPropertyId = $propertyId->getNumericId();

			$dataValue = $claim->getMainSnak()->getDataValue();

			$res = $dbr->select(
				'wbq_constraints_from_templates',                     // $table
				array('pid', 'constraint_name', 'min', 'max'),        // $vars (columns of the table)
				("pid = $numericPropertyId"),                         // $conds
				__METHOD__,                                           // $fname = 'Database::select',
				array('')                                             // $options = array()
			);

			foreach ($res as $row) {

				switch ($row->constraint_name) {
					case 'Single value':
						$this->checkSingleValueConstraint($propertyId, $dataValue);
						break;
					case 'Range':
						$this->checkRangeConstraint($propertyId, $dataValue, $row->min, $row->max);
						break;
					case 'Symmetric':
						$this->checkSymmetricConstraint($propertyId, $dataValue);
						break;
					default:
						//not yet implemented cases, also error case
						$out->addWikiText("The claim {{Property:$propertyId}}: $dataValue has a {{tl|$row->constraint_name}}, but there is no check implemented yet.\n\n");
						break;
				}

			}

		}
		
	}
	
	function entityFromPar($parameter) {
		global $lookup;

		switch(strtoupper($parameter[0])) {
		case 'Q':
			return $lookup->getEntity(new ItemId($parameter));
		case 'P':
			return $lookup->getEntity(new PropertyId($parameter));
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
	
}