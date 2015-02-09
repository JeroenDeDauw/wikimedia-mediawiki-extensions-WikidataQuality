<?php

namespace WikidataQuality\ConstraintReport\Specials;

use SpecialPage;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Repo\Store;
use Wikibase\DataModel\Statement;
use Wikibase\DataModel\Snak;

//TODO (prio high): define tests for the checks against constraints (test items with statements)
//TODO (prio high): add support for remaining constraints (some might use a common set of methods):
/*	[todo]	Commons link
 *	[todo]	Conflicts with - similar to Target required claim (target is self)
 *	[DONE]	Diff within range
 *	[todo]	Format
 *	[todo]	Inverse - special case of Target required claim
 *	[todo]	Item
 *	[DONE]	Multi value - similar to Single value
 *	[DONE]	One of
 *	[DONE]	Qualifier
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
//TODO (prio normal): check for exceptions and mark a statement as such
//TODO (prio normal): handle qualifiers, e.g. on a property violating the single value constraint, although every value was only valid at a certain point in time
//TODO (prio normal): handle constraint parameter 'now' when dealing with time values
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
        return $this->msg( 'wikidataquality-constraintreport' )->text();
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
        $out->addHTML( "<p>Enter an Item or a Property ID to check the corresponding Entity's statements against Constraints.</p>" );
        $out->addHTML( "<form name='EntityIdForm' action='" . $_SERVER['PHP_SELF'] . "' method='post'>" );
        $out->addHTML( "<input placeholder='Qxx/Pxx' name='entityID' id='entity-input'>" );
        $out->addHTML( "<input type='submit' value='Check' />" );
        $out->addHTML( "</form><br /><br />" );

        if( !isset($_POST['entityID']) || strlen($_POST['entityID']) == 0 ) {
            return;
        }

        $entity = $this->entityFromParameter( $_POST['entityID'] );
        if( $entity == null ) {
            $out->addWikiText( "No valid entityID given or entity does not exist: " . $_POST['entityID'] . "\n" );
            return;
        }

        $out->addHTML( '<h2>Constraint report for ' . $entity->getType() . ' ' . $entity->getId() . ' (' . $entity->getLabel('en') . '):</h2><br />' );

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
            . "! Property !! class=\"unsortable\" | Value !! Constraint !! class=\"unsortable\" | Parameters !! Status\n";

        foreach( $entityStatements as $statement ) {

            $claim = $statement->getClaim();

            $propertyId = $claim->getPropertyId();
            $numericPropertyId = $propertyId->getNumericId();

            $mainSnak = $claim->getMainSnak();
            if( $mainSnak->getType() == 'value' ) {
                $dataValueString = $this->dataValueToString( $mainSnak->getDataValue() );
            } else {
                $dataValueString = '\'\'(' . $mainSnak->getType() . '\'\')';
            }

            $res = $dbr->select(
                'wbq_constraints_from_templates',											                    // $table
                array('pid', 'constraint_name', 'base_property', 'exceptions', 'max', 'min', 'values_'),		// $vars (columns of the table)
                ("pid = $numericPropertyId"),												                    // $conds
                __METHOD__,																	                    // $fname = 'Database::select',
                array('')																	                    // $options = array()
            );

            foreach( $res as $row ) {

                $this->output .= "|-\n";

                switch( $row->constraint_name ) {
                    case 'Diff within range':
                        $this->checkDiffWithinRangeConstraint( $propertyId, $dataValueString, $row->base_property, $row->min, $row->max, $entityStatements );
                        break;
                    case 'Multi value':
                        $this->checkMultiValueConstraint( $propertyId, $dataValueString, $propertyCount );
                        break;
                    case 'One of':
                        $this->checkOneOfConstraint( $propertyId, $dataValueString, $row->values_ );
                        break;
                    case 'Qualifier':
                        $this->checkQualifierConstraint( $propertyId, $dataValueString );
                        break;
                    case 'Range':
                        $this->checkRangeConstraint( $propertyId, $dataValueString, $row->min, $row->max );
                        break;
                    case 'Single value':
                        $this->checkSingleValueConstraint( $propertyId, $dataValueString, $propertyCount );
                        break;
                    default:
                        //not yet implemented cases, also error case
                        $this->addOutputRow( $propertyId, $dataValueString, $row->constraint_name, '', 'todo' );
                        break;
                }

            }

        }

        $this->output .= "|-\n|}";
        $out->addWikiText($this->output);
    }

    function entityFromParameter( $parameter ) {
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

    function checkDiffWithinRangeConstraint( $propertyId, $dataValueString, $basePropertyId, $min, $max, $entityStatements ) {
        $parameterString = 'Base Property: ' . $basePropertyId . ', min: ' . $min . ', max: ' . $max;

        foreach( $entityStatements as $statement ) {
            if( $basePropertyId == $statement->getClaim()->getPropertyId() ) {
                $mainSnak = $statement->getClaim()->getMainSnak();

                if( $mainSnak->getType() == 'value' ) {
                    $basePropertyDataValueString = $this->dataValueToString( $mainSnak->getDataValue() );

                    $diff = abs( $dataValueString-$basePropertyDataValueString );

                    if( $diff < $min || $diff > $max ) {
                        $status = 'violation';
                    } else {
                        $status = 'compliance';
                    }
                } else {
                    $status = 'violation';
                }

                $this->addOutputRow( $propertyId, $dataValueString, 'Diff within range', $parameterString, $status );
            }
        }
    }

    function checkMultiValueConstraint( $propertyId, $dataValueString, $propertyCount ) {
        if( $propertyCount[$propertyId->getNumericId()] <= 1 ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        $this->addOutputRow( $propertyId, $dataValueString, 'Multi value', '\'\'(none)\'\'', $status );
    }

    function checkOneOfConstraint( $propertyId, $dataValueString, $values ) {
        $toReplace = array("{", "}", "|", "[", "]", " ");
        $allowedValues = explode(",", str_replace($toReplace, "", $values));

        if( !in_array($dataValueString, $allowedValues) ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        $showMax = 5;
        if( sizeof($allowedValues) <= $showMax ) {
            $parameterString = 'values: ' . implode(", ", $allowedValues);
        } else {
            $parameterString = 'values: ' . implode(", ", array_slice($allowedValues, 0, $showMax)) . ' \'\'(and ' . (sizeof($allowedValues)-$showMax) . ' more)\'\'';
        }

        $this->addOutputRow( $propertyId, $dataValueString, 'One of', $parameterString, $status );
    }

    function checkQualifierConstraint( $propertyId, $dataValueString ) {
        $this->addOutputRow( $propertyId, $dataValueString, 'Qualifier', '\'\'(none)\'\'', 'violation' );
    }

    function checkRangeConstraint( $propertyId, $dataValueString, $min, $max ) {
        if( $dataValueString < $min || $dataValueString > $max ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        $parameterString = 'min: ' . $min . ', max: ' . $max;

        $this->addOutputRow( $propertyId, $dataValueString, 'Range', $parameterString, $status );
    }

    function checkSingleValueConstraint( $propertyId, $dataValueString, $propertyCount ) {
        if( $propertyCount[$propertyId->getNumericId()] > 1 ) {
            $status = 'violation';
        } else {
            $status = 'compliance';
        }

        $this->addOutputRow( $propertyId, $dataValueString, 'Single value', '\'\'(none)\'\'', $status );
    }

    function dataValueToString( $dataValue ) {
        $dataValueType = $dataValue->getType();
        switch( $dataValueType ) {
            case 'string':
            case 'decimal':
            case 'number':
            case 'boolean':
            case 'unknown':
                return $dataValue->getValue();
            case 'quantity':
                return $dataValue->getAmount()->getValue();
            case 'time':
                return $dataValue->getTime();
            case 'globecoordinate':
            case 'geocoordinate':
                return 'Latitude: ' . $dataValue->getLatitude() . ', Longitude: ' . $dataValue->getLongitude();
            case 'monolingualtext':
                return $dataValue->getText();
            case 'multilingualtext':
                if( array_key_exists('en', $dataValue) ) {
                    return $dataValue->getTexts()['en'];
                } else {
                    return array_shift($dataValue->getTexts());
                };
            case 'wikibase-entityid':
                return $dataValue->getEntityId();
            case 'bad':
            default:
                //error case
        }
    }

    function addOutputRow( $propertyId, $dataValueString, $constraintName, $parameterString, $status ) {
        $lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();

        $this->output .=
            "| " . $propertyId . " (" . $lookup->getEntity($propertyId)->getLabel('en') . ") "
            . "|| " . $dataValueString . " "
            . "|| " . $constraintName . " "
            . "|| " . $parameterString . " ";
        switch( $status ) {
            case 'compliance':
                $this->output .= "|| <div style=\"color:#088A08\">compliance <b>(+)</b></div>\n";
                break;
            case 'violation':
                $this->output .= "|| <div style=\"color:#8A0808\">violation <b>(-)</b></div>\n";
                break;
            case 'exception':
                $this->output .= "|| <div style=\"color:#D2D20C\">exception <b>(+)</b></div>\n";
                break;
            case 'todo':
                $this->output .= "|| <div style=\"color:#808080\">not yet implemented <b>:(</b></div>\n";
                break;
            default:
                //error case
        }
    }

}