<?php

namespace WikidataQuality\ConstraintReport\Specials;

use SpecialPage;
use Html;
use WikidataQuality\ConstraintReport\ConstraintCheck\ConstraintChecker;
use Wikibase\Repo\WikibaseRepo;


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
            $out->addHTML( Html::openElement( 'br' ) . Html::openElement( 'h1' ) . $this->msg( 'wikidataquality-constraint-result-headline' ) . $_POST['entityID'] .  Html::closeElement( 'h1' ) );
            $this->output .= $this->getTableHeader();
            foreach( $results as $checkResult) {
                $this->addOutputRow( $checkResult );
            }
            $this->output .= "|-\n|}"; // close Table
            $out->addWikiText($this->output);
            return;
        } else {
            $out->addHTML(Html::openElement( 'p' ) . $this->msg( 'wikidataquality-constraint-result-entity-not-existent')->text(). Html::closeElement( 'p' ) );
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
        $lookup = WikibaseRepo::getDefaultInstance()->getEntityLookup();
        $this->output .=
            "|-\n"
            . "| " . $result->getPropertyId() . " (" . $lookup->getEntity($result->getPropertyId())->getLabel('en') . ") "
            . "|| " . $result->getDataValue() . " "
            . "|| " . $result->getConstraintName() . " "
            . "|| " . $result->getParameter() . " ";

        switch( $result->getStatus() ) {
            case 'compliance':
                $color = '#088A08';
                break;
            case 'violation':
                $color = '#8A0808';
                break;
            case 'exception':
                $color = '#D2D20C';
                break;
            case 'todo':
                $color = '#808080';
                break;
            case 'fail':
                $color = '#808080';
                break;
            default:
                $color = '#0D0DE0';
                //error case; should not happen
        }
        $this->output .= "|| <div style=\"color:" . $color . "\">" . $result->getStatus() . "</div>\n";
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