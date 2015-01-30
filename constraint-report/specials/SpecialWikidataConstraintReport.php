<?php

namespace WikidataQuality\ConstraintReport\Specials;

use SpecialPage;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Repo\Store;
use Wikibase\DataModel\Statement;
use Wikibase\DataModel\Snak;

//TODO (prio low): fix the table, having a sixth, empty, column
//TODO (prio high): define tests for the checks against constraints (test items with statements)
//TODO (prio high): add support for remaining constraints (some might use a common set of methods):
	/*	[todo]	Commons link
	 *	[todo]	Conflicts with - similar to Target required claim (target is self)
	 *	[todo]	Diff within range - similar to Range
	 *	[todo]	Format
	 *	[todo]	Inverse - special case of Target required claim
	 *	[todo]	Item
	 *	[DONE]	Multi value - similar to Single value
	 *	[DONE]	One of
	 *	[todo]	Qualifier
	 *	[todo]	Qualifiers
	 *	[DONE]	Range
	 *	[DONE]	Single value - similar to Multi value
	 *	[todo]	Symmetric - special case of Inverse, which is a special case of Target required claim
	 *	[todo]	Target required claim
	 *	[todo]	Type - similar to Value type
	 *	[todo]	Unique value
	 *	[todo]	Value type - similar to Type
	 */
//TODO (prio normal): add templates for items, properties, constraints to our instance and write them like {{Q|1234}} or [[Property:P567]] or {{tl|Constraint:Range}} or ... in this code
//TODO (prio normal): refactor this code, so that output creation (which is similar for all constraints) is done by one method
//TODO (prio normal): refactor this code, so that finding the value of a statement (which is similar for all statements/claims) is done by one method
//TODO (prio normal): check for exceptions and mark a statement as such, also handle qualifiers
//TODO (prio low): handle output for the edge case, where there are no constraints defined on an entity's statements (as is the case for many properties)
//TODO (prio low): find visualizations other than a table
//TODO (prio low): add auto-completion/suggestions while typing to the input form
//TODO (prio low): go through the warnings and refactor this code accordingly


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

	private $output = '';

	/**
	 * @see SpecialPage::execute
	 *
	 * @param string|null $par
	 */
	function execute( $par ) {
		$this->setHeaders();
		$out = $this->getContext()->getOutput();

		// Show form
		$out->addHTML( "<p>Enter an Item or a Property ID to check the corresponding Entity's statements against Constraints.</p>");
        $out->addHTML( "<form name='EntityIdForm' action='" . $_SERVER['PHP_SELF'] . "' method='post'>" );
        $out->addHTML( "<input placeholder='Qxx/Pxx' name='entityID' id='entity-input'>" );
		$out->addHTML( "<input type='submit' value='Check' />" );
		$out->addHTML( "</form><br /><br />" );

		if( !isset($_POST['entityID']) || strlen($_POST['entityID']) == 0 ) {
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

		$this->output .=
			"{| class=\"wikitable sortable\"\n"
			. "! Property !! class=\"unsortable\" | Value !! Constraint !! class=\"unsortable\" | Parameters !! Status\n"
		;

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

			$lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();

			foreach( $res as $row ) {

				$this->output .= "|-\n";

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
						$this->output .=
							"| " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . ") "
							. "|| "
							. "|| " . $row->constraint_name . " "
							. "|| "
							. "|| <font color=\"#808080\">not yet implemented <b>:(</b></font> ||\n"
						;
						break;
				}

			}

		}

		$this->output .= "|-\n|}";
		$out->addWikiText($this->output);
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
		$lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();

		$this->output .=
			"| " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . ") "
			. "|| "
			. "|| Multi value "
			. "|| (none) ";
		if( $propertyCount[$propertyId->getNumericId()] <= 1 ) {
			$this->output .= "|| <font color=\"#8A0808\">violation <b>(-)</b></font> ||\n";
		} else {
			$this->output .= "|| <font color=\"#088A08\">compliance <b>(+)</b></font> ||\n";
		}
	}

	function checkOneOfConstraint( $propertyId ,$dataValue, $values ) {
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

		$this->output .=
			"| " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . ") "
			. "|| " . $value->getEntityId() . " "
			. "|| One of "
			. "|| "// . $values . " "
		;

		$valueFound = false;
		foreach( $allowedValues as $value ) {
			if( in_array($value,$allowedValues) ) {
				$this->output .= "|| <font color=\"#088A08\">compliance <b>(+)</b></font> ||\n";
				$valueFound = true;
				break;
			}
		}

		if( !$valueFound ) {
			$this->output .= "|| <font color=\"#8A0808\">violation <b>(-)</b></font> ||\n";
		}
	}

	function checkRangeConstraint( $propertyId ,$dataValue, $min, $max ) {
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
				$value = 2014;
				//error case, maybe value is 'now';
				//$value = 2015; //todo: make this work with 'now'
		}

		$lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();

		$this->output .=
			"| " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . ") "
			. "|| " . $value . " "
			. "|| Range "
			. "|| min " . $min . ", max " . $max . " ";
		if( $value < $min || $value > $max ) {
			$this->output .= "|| <font color=\"#8A0808\">violation <b>(-)</b></font> ||\n";
		} else {
			$this->output .= "|| <font color=\"#088A08\">compliance <b>(+)</b></font> ||\n";
		}
	}

	function checkSingleValueConstraint( $propertyId, $propertyCount ) {
		$lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();

		$this->output .=
			"| " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . ") "
			. "|| "
			. "|| Single value "
			. "|| (none) ";
		if( $propertyCount[$propertyId->getNumericId()] > 1 ) {
			$this->output .= "|| <font color=\"#8A0808\">violation <b>(-)</b></font> ||\n";
		} else {
			$this->output .= "|| <font color=\"#088A08\">compliance <b>(+)</b></font> ||\n";
		}
	}

}