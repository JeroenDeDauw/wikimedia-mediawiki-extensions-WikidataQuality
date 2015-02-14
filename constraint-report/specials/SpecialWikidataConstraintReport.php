<?php

namespace WikidataQuality\ConstraintReport\Specials;

use SpecialPage;
use Html;
use WikidataQuality\ConstraintReport\ConstraintCheck\ConstraintChecker;
use Wikibase\Repo\WikibaseRepo;

//TODO (prio high): define tests for the checks against constraints (test items with statements)
//TODO (prio high): add support for remaining constraints (some might use a common set of methods):
/*	[todo]	Commons link
 *	[todo]	Conflicts with - similar to Target required claim (target is self)
 *	[DONE]	Diff within range
 *	[todo]	Format
 *	[DONE]	Inverse - special case of Target required claim
 *	[todo]	Item
 *	[DONE]	Multi value - similar to Single value
 *	[DONE]	One of
 *	[DONE]	Qualifier
 *	[todo]	Qualifiers
 *	[DONE]	Range
 *	[DONE]	Single value - similar to Multi value
 *	[DONE]	Symmetric - special case of Inverse, which is a special case of Target required claim
 *	[DONE]	Target required claim
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

    private $output = '';

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

    /**
     * @see SpecialPage::execute
     *
     * @param string|null $par
     */
    function execute( $par ) {
        // Build cross-check form
        $this->setHeaders();

        // Get output
        $out = $this->getOutput();

        $out->addHTML( $this->getHtmlForm() );

        if( !empty($_POST['entityID'] ) ) {
            $constraintChecker = new ConstraintChecker();
            $results = $constraintChecker->execute( $_POST['entityID'] );
        } else {
            return;
        }

        if( $results ) {
            $this->output .= $this->getTableHeader();
            foreach( $results as $checkResult) {
                $this->addOutputRow( $checkResult );
            }
            $this->output .= "|-\n|}"; // close Table
            $out->addWikiText($this->output);
            return;
        }

    }

    private function getHtmlForm()
    {
        return Html::openElement( 'p' )
                . $this->msg( 'wikidataquality-constraint-instructions' )->text()
                . Html::element( 'br' )
                . $this->msg( 'wikidataquality-constraint-instructions-example' )->text()
                . Html::closeElement( 'p' )
                . Html::openElement(
                    'form',
                    array(
                        'action' => $_SERVER[ 'PHP_SELF' ],
                        'method' => 'post'
                    )
                )
                . Html::input(
                    'entityID',
                    '',
                    'text',
                    array(
                        'id' => 'wdq-constraint-entityId',
                        'placeholder' => $this->msg( 'wikidataquality-constraint-form-id-placeholder' )->text()
                    )
                )
                . Html::input(
                    'submit',
                    $this->msg( 'wikidataquality-constraint-form-submit-label' )->text(),
                    'submit',
                    array(
                        'id' => 'wbq-constraint-submit'
                    )
                )
                . Html::closeElement( 'form' );
    }




    function addOutputRow( $result ) {
        $lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();
        $this->output .=
            "|-\n"
            . "| " . $result->getPropertyId() . " (" . $lookup->getEntity($result->getPropertyId())->getLabel('en') . ") "
            . "|| " . $result->getDataValue() . " "
            . "|| " . $result->getConstraintName() . " "
            . "|| " . $result->getParameter() . " ";
        switch( $result->getStatus() ) {
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
            case 'fail':
            default:
                $this->output .= "|| <div style=\"color:#808080\">check failed <b>:(</b></div>\n";
                //error case
        }
    }

    /**
     * @return string
     */
    private function getTableHeader()
    {
        return "{| class=\"wikitable sortable\"\n"
            . "! Property !! class=\"unsortable\" | Value !! Constraint !! class=\"unsortable\" | Parameters !! Status\n";

    }

}