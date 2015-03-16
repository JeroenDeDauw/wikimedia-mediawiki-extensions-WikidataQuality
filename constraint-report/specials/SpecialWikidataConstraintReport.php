<?php

namespace WikidataQuality\ConstraintReport\Specials;

use Html;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use DataValues;
use Wikibase\Repo\WikibaseRepo;
use WikidataQuality\ConstraintReport\ConstraintCheck\ConstraintChecker;
use WikidataQuality\Specials\SpecialWikidataQualityPage;

class SpecialWikidataConstraintReport extends SpecialWikidataQualityPage {

    protected $entityLookup;
    private $output = '';

    function __construct() {
        parent::__construct( 'ConstraintReport' );
        $this->entityLookup = WikibaseRepo::getDefaultInstance()->getEntityLookup();
    }

    /**
     * @see SpecialPage::getGroupName
     *
     * @return string
     */
    function getGroupName() {
        return 'wikidataquality';
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
    public function execute( $par ) {
        $this->setHeaders();

        // Get output
        $out = $this->getOutput();

        $out->addHTML( $this->getHtmlForm() );

        if( !empty( $_POST['entityID'] ) ) {
            $constraintChecker = new ConstraintChecker();
            $entityId = $this->getEntityID( $_POST['entityID'] );
            $entity = $this->entityLookup->getEntity( $entityId );
            $results = $constraintChecker->execute( $entity );
        } else {
            return;
        }

        if( $results ) {
            $out->addHTML( Html::openElement( 'br' ) . Html::openElement( 'h1' )
                . $this->msg( 'wikidataquality-constraint-result-headline' )
                . $this->entityIdHtmlLinkFormatter->formatEntityId( $entityId )
                . ' (<nowiki>' . $entityId . '</nowiki>)'
                . Html::closeElement( 'h1' ) );
            $this->output .= $this->getTableHeader();
            foreach( $results as $checkResult) {
                $this->addOutputRow( $checkResult );
            }
            $this->output .= "|-\n|}"; // close table
            $out->addWikiText( $this->output );
            return;
        } else {
            $out->addHTML( Html::openElement( 'p' )
                . $this->msg( 'wikidataquality-constraint-result-entity-not-existent' )->text()
                . Html::closeElement( 'p' ) );
        }

    }

    private function getHtmlForm() {
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

    private function getEntityID( $entityId )
    {
        switch( strtoupper( $entityId[0] ) ) {
            case 'Q':
                return new ItemId( $entityId );
            case 'P':
                return new PropertyId( $entityId );
            default:
                return null;
        }
    }

    private function addOutputRow( $result ) {
        $this->output .=
            "|-\n"
            . "| " . $this->entityIdLinkFormatter->formatEntityId( $result->getPropertyId() )
            . "|| " . $this->formatValue( $result->getDataValue() ) . " "
            . "|| " . $result->getConstraintName() . " "
            . "|| " . $this->formatParameters( $result->getParameters() ) . " ";

        switch( $result->getStatus() ) {
            case 'compliance':  // constraint has been checked, result is positive
                $color = '#088A08';
                break;
            case 'exception':   // the statement violates the constraint, but is a known exception
                $color = '#D2D20C';
                break;
            case 'violation':   // constraint has been checked, result is negative
            case 'error':       // there was an error in the definition of the constraint, e.g. missing or wrong parameters
            case 'fail':        // the check failed, e.g. because a referenced item doesn't exist
                $color = '#8A0808';
                break;
            case 'todo':        // the constraint check has not yet been implemented
                $color = '#808080';
                break;
            default:            // error case, should not happen
                $color = '#0D0DE0';
        }
        $this->output .= "|| <div style=\"color:" . $color . "\">" . $result->getStatus() . "</div>\n";
    }

    /**
     * @param mixed string|ItemId|PropertyId|DataValues\DataValue $dataValue
     *
     * @return string
     */
    private function formatValue( $dataValue ) {
        if( is_string( $dataValue ) ) { // cases like 'Format' 'pattern' or 'minimum'/'maximum' values, which we have stored as strings
            return ( '<nowiki>' . $dataValue . '</nowiki>' );
        } else if( get_class( $dataValue ) === 'Wikibase\DataModel\Entity\ItemId' || get_class( $dataValue ) === 'Wikibase\DataModel\Entity\PropertyId' ) { // cases like 'Conflicts with' 'property', to which we can link
            return $this->entityIdLinkFormatter->formatEntityId( $dataValue );
        } else { // cases where we format a DataValue
            if ( $dataValue->getType() === 'wikibase-entityid' ) { // Entities, to which we can link
                return $this->entityIdLinkFormatter->formatEntityId( $dataValue->getEntityId() );
            } else { // other DataValues, which can be formatted
                return $this->dataValueFormatter->format( $dataValue );
            }
        }
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    private function formatParameters( $parameters ) {
        $formattedParameters = '';
        $parameterNames = array_keys( $parameters );

        foreach( $parameterNames as $parameterName ) {
            $formattedParameters .= ( $parameterName . ': ' );
            $parameterValue = $parameters[$parameterName];

            $formattedParameters .= implode( ', ', $this->limitArrayLength( array_map( array( 'self', 'formatValue' ), $parameterValue ) ) );

            $formattedParameters .= '<br />';
        }

        return $formattedParameters;
    }

    private $maxArrayLength = 5;

    private function limitArrayLength( $array ) {
        if( count( $array ) > $this->maxArrayLength ) {
            $array = array_slice( $array, 0, $this->maxArrayLength );
            array_push( $array, '...' );
        }
        return $array;
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