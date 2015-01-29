<?php

namespace WikidataQuality\ConstraintReport\Specials;

use SpecialPage;
use Wikibase\DataModel\Entity\ItemId;
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
		$this->setHeaders();
		$out = $this->getContext()->getOutput();

		// Show form
		$out->addHTML( '<p>Enter an Item or a Property ID to check the corresponding Entity\'s statements against Constraints.<br />'
            . 'Try for example <i>Q46</i> (Europe)<sup>Range</sup>, <i>Q60</i> (New York City)<sup>Range, One of</sup>, <i>Q80</i> (Tim Berners-Lee)<sup>2x One of</sup> or some <i>Pxx</i> (XYZ)</p>'
        );
        $out->addHTML( "<form name='EntityIdForm' action='" . $_SERVER['PHP_SELF'] . "' method='post'>" );
        $out->addHTML( "<input placeholder='Qxx/Pxx' name='entityID' id='entity-input'>" );
		$out->addHTML( "<input type='submit' value='Check' />" );
		$out->addHTML( "</form><br /><br />" );

		if( !isset($_POST['entityID']) ) {
			return;
		}

		$entity = $this->entityFromPar($_POST['entityID']);
		if( $entity == null ) {
			$out->addWikiText("No valid entityID given or entity does not exist: " . $_POST['entityID'] . "\n");
			return;
		}

		$out->addHTML( '<h2>Constraint report for ' . $entity->getType() . ' ' . $entity->getId() . ' (' . $entity->getLabel('en') . '):</h2><br />');

		$entityStatements = $entity->getStatements();
		$entityStatementsArray = $entityStatements->toArray();
		$propertyCount = array();
		foreach( $entityStatementsArray as $entityStatement ) {
			if( array_key_exists($entityStatement->getPropertyId()->getNumericId(), $propertyCount) ) {
				$propertyCount[$entityStatement->getPropertyId()->getNumericId()]++;
			} else {
				$propertyCount[$entityStatement->getPropertyId()->getNumericId()] = 0;
			}
		}

		$dbr = wfGetDB( DB_SLAVE );

		foreach( $entityStatements as $statement ) {

			$claim = $statement->getClaim();

			$propertyId = $claim->getPropertyId();
			$numericPropertyId = $propertyId->getNumericId();

			$dataValue = $claim->getMainSnak()->getDataValue();

			$res = $dbr->select(
				'wbq_constraints_from_templates',								// $table
				array('pid', 'constraint_name', 'min', 'max', 'values_'),		// $vars (columns of the table)
				("pid = $numericPropertyId"),									// $conds
				__METHOD__,														// $fname = 'Database::select',
				array('')														// $options = array()
			);

			foreach( $res as $row ) {

				switch( $row->constraint_name ) {
					case 'Multi value':
						$this->checkMultiValueConstraint($propertyId, $propertyCount);
						break;
					case 'One of':
						$this->checkOneOfConstraint($propertyId, $dataValue, $row->values_);
						break;
					case 'Range':
						$this->checkRangeConstraint($propertyId, $dataValue, $row->min, $row->max);
						break;
					case 'Single value':
						$this->checkSingleValueConstraint($propertyId, $propertyCount);
						break;
					default:
						//not yet implemented cases, also error case
						$out->addWikiText("Property " . $propertyId . " has a " . $row->constraint_name . " Constraint, but there is no check implemented yet. :(\n");
						break;
				}

			}

		}
		
	}
	
	function entityFromPar($parameter) {
		$lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();

		switch(strtoupper($parameter[0])) {
		case 'Q':
			return $lookup->getEntity(new ItemId($parameter));
		case 'P':
			return $lookup->getEntity(new PropertyId($parameter));
		default:
			return null;
		}
	}

	function checkMultiValueConstraint( $propertyId, $propertyCount ) {
		$output = '';

		$lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();
		$out = $this->getContext()->getOutput();

		if( $propertyCount[$propertyId->getNumericId()] <= 1 ) {
			$out->addHTML("<div style='color: red'>");
			$output .= "'''VIOLATION:''' ''The Claim using Property " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . ") violates the Multi value Constraint.''\n";
		} else {
			$out->addHTML("<div style='color: green'>");
			$output .= "''The Claim using Property " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . ") complies with the Multi value Constraint.''\n";
		}


		$out->addWikiText($output);
		$out->addHTML("</div>");
	}

	function checkOneOfConstraint( $propertyId ,$dataValue, $values ) {
		$output = '';

		$dataValueType = $dataValue->getValue()->getType();
		switch( $dataValueType ) {
			case 'wikibase-entityid':
				$value = $dataValue->getValue();
				break;
			case 'quantity':
				$value = $dataValue->getAmount()->getValue();
				break;
			default:
				//error case
		}

		$toReplace = array("{", "}", "|", "[", "]", " ");
		$allowedValues = explode(",", str_replace($toReplace,"",$values));

		$lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();
		$out = $this->getContext()->getOutput();
		$valueFound = false;
		foreach( $allowedValues as $value ) {
			if( in_array($value,$allowedValues) ) {
				$out->addHTML("<div style='color: green'>");
				$output .= "''The Claim [Property " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . "): " . $value . "] complies with the One of Constraint [values " . $values . "].''\n";
				$valueFound = true;
				break;
			}
		}

		if( !$valueFound ) {
			$out->addHTML("<div style='color: red'>");
			$output .= "'''VIOLATION:''' ''The Claim [Property " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . "): " . $value . "] violates the One of Constraint [values " . $values . "].''\n";
		}

		$out->addWikiText($output);
		$out->addHTML("</div>");
	}

	function checkRangeConstraint( $propertyId ,$dataValue, $min, $max ) {
		$output = '';

		$dataValueType = $dataValue->getValue()->getType();
		switch( $dataValueType ) {
			case 'decimal':
			case 'number':
				$value = $dataValue->getValue();
				break;
			case 'quantity':
				$value = $dataValue->getAmount()->getValue();
				break;
			default:
				//error case, maybe value is 'now';
				$value = 2015; //todo: make this work with 'now'
		}

		$lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();

		$out = $this->getContext()->getOutput();
		if( $value < $min || $value > $max ) {
			$out->addHTML("<div style='color: red'>");
			$output .= "'''VIOLATION:''' ''The Claim [Property " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . "): " . $value . "] violates the Range Constraint [min " . $min . ", max " . $max . "].''\n";
		} else {
			$out->addHTML("<div style='color: green'>");
			$output .= "''The Claim [Property " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . "): " . $value . "] complies with the Range Constraint [min " . $min . ", max " . $max . "].''\n";
		}

		$out->addWikiText($output);
		$out->addHTML("</div>");
	}

	function checkSingleValueConstraint( $propertyId, $propertyCount ) {
		$output = '';

		$lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();
		$out = $this->getContext()->getOutput();

		if( $propertyCount[$propertyId->getNumericId()] > 1 ) {
			$out->addHTML("<div style='color: red'>");
			$output .= "'''VIOLATION:''' ''The Claim using Property " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . ") violates the Single value Constraint.''\n";
		} else {
			$out->addHTML("<div style='color: green'>");
			$output .= "''The Claim using Property " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . ") complies with the Single value Constraint.''\n";
		}
		$out->addWikiText($output);
		$out->addHTML("</div>");
	}

}