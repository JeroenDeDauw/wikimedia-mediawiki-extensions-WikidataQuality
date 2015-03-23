<?php

namespace WikidataQuality\ConstraintReport\Specials;

use Html;
use Wikibase\DataModel;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use DataValues;
use Wikibase\Repo\WikibaseRepo;
use WikidataQuality\ConstraintReport\ConstraintCheck\ConstraintChecker;
use WikidataQuality\Specials\SpecialWikidataQualityPage;
use WikidataQuality\Html\HtmlTable;

/**
 * Class SpecialWikidataConstraintReport
 * Special page that displays all constraints that are defined on an Entity with additional information
 * (whether it complied or was a violation, which parameters the constraint has etc.).
 * @package WikidataQuality\ConstraintReport\Specials
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class SpecialWikidataConstraintReport extends SpecialWikidataQualityPage {

    /**
     * Wikibase entity lookup.
     * @var \Wikibase\Lib\Store\EntityLookup
     */
    protected $entityLookup;

    /**
     * The entity we want to check.
     * @var \Wikibase\DataModel\Entity\Entity
     */
    private $entity;

    /**
     * Variable to collect output before adding it to $out.
     * @var string
     */
    private $output = '';

    /**
     * @var int
     */
    private $maxParameterArrayLength = 5;

    function __construct() {
        parent::__construct( 'ConstraintReport' );
        $this->entityLookup = WikibaseRepo::getDefaultInstance()->getEntityLookup();
    }

    /**
     * @see SpecialPage::getGroupName
     * @return string
     */
    function getGroupName() {
        return 'wikidataquality';
    }

    /**
     * @see SpecialPage::getDescription
     * @return string
     */
    public function getDescription() {
        return $this->msg( 'wikidataquality-constraintreport' )->text();
    }

    /**
     * @see SpecialPage::execute
     * @param string|null $par
     */
    public function execute( $par ) {
        // get output
        $out = $this->getOutput();

        // add tooltip style
        $out->addModuleStyles( 'SpecialConstraintReport' );

        $this->setHeaders();

        $out->addHTML( $this->getHtmlForm() );

        if( !empty( $_POST['entityID'] ) ) {
            $constraintChecker = new ConstraintChecker();
            $entityId = $this->getEntityID( $_POST['entityID'] );
            $this->entity = $this->entityLookup->getEntity( $entityId );
            $results = $constraintChecker->execute( $this->entity );
        } else {
            return;
        }

        if( $results ) {
            $out->addHTML( Html::openElement( 'br' ) . Html::openElement( 'h3' )
                . $this->msg( 'wikidataquality-constraint-result-headline' )->text()
                . $this->entityIdHtmlLinkFormatter->formatEntityId( $entityId )
                . ' (<nowiki>' . $entityId . '</nowiki>)'
                . Html::closeElement( 'h3' ) );

            $out->addHTML( $this->buildResultTable( $results )->toHtml() );
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

    /**
     * @param $entityId
     * @return null|ItemId|PropertyId
     */
    private function getEntityID( $entityId ) {
        switch( strtoupper( $entityId[0] ) ) {
            case 'Q':
                return new ItemId( $entityId );
            case 'P':
                return new PropertyId( $entityId );
            default:
                return null;
        }
    }

    /**
     * @param array $results
     * @return HtmlTable
     */
    private function buildResultTable( $results ) {
        $table = new HtmlTable(
            array(
                $this->msg( 'wikidataquality-constraint-result-table-header-status' )->text(),
                $this->msg( 'wikidataquality-constraint-result-table-header-claim' )->text(),
                $this->msg( 'wikidataquality-constraint-result-table-header-constraint' )->text(),
            ),
            true
        );

        $tooltipIndicator = '<span style="color:#CCC; font-weight:600">[?]</span>';

        $namespace = ( $this->entity instanceof \Wikibase\DataModel\Entity\Item ) ? 'Item' : 'Property';

        foreach ( $results as $result ) {
            switch( $result->getStatus() ) {
                case 'compliance':  // constraint has been checked, result is positive
                    $color = '#088A08';
                    break;
                case 'exception':   // the statement violates the constraint, but is a known exception
                    $color = '#D2D20C';
                    break;
                case 'todo':        // the constraint check has not yet been implemented
                    $color = '#808080';
                    break;
                case 'violation':   // constraint has been checked, result is negative
                    $color = '#BA0000';
                    break;
                default:            // error case, should not happen
                    $color = '#404040';
            }

            if( $result->getMessage() !== '' ) {
                $status_tooltip = $result->getMessage();
                $statusColumn = '<div tooltip="' . $status_tooltip . '"><span style="color:' . $color . '">' . $result->getStatus() . '</span> ' . $tooltipIndicator . '</div>';
            } else {
                $statusColumn = '<span style="color:' . $color . '">' . $result->getStatus() . '</span>';
            }

            $property = $this->entityIdHtmlLinkFormatter->formatEntityId( $result->getPropertyId() );
            $value = $this->formatValue( $result->getDataValue() );
            $claimUrl = './' . $namespace . ':' . $this->entity->getId()->getSerialization() . '#' . $result->getPropertyId()->getSerialization();
            $claimLink = '<a href="' . $claimUrl . '" target="_blank">' . $this->msg( 'wikidataquality-constraint-result-link-to-claim' )->text() . '</a>';
            $claimColumn = $property . ': ' . $value . ' (' . $claimLink . ')';

            $constraintUrl = './Property:' . $result->getPropertyId()->getSerialization() . '#' . '';
            $constraintLink = '<a href="' . $constraintUrl . '" target="_blank">' . $this->msg( 'wikidataquality-constraint-result-link-to-constraint' )->text() . '</a>';
            if( count( $result->getParameters() ) !== 0 ) {
                $constraint_tooltip = $this->formatParameters( $result->getParameters() );
                $constraintColumn = '<div tooltip="' . $constraint_tooltip . '">' . $result->getConstraintName() . ' (' . $constraintLink . ') ' . $tooltipIndicator . '</div> ';
            } else {
                $constraintColumn = $result->getConstraintName() . ' (' . $constraintLink . ') ';
            }

            // Body of table
            $table->appendRow(
                array(
                    $statusColumn,
                    $claimColumn,
                    $constraintColumn
                )
            );
        }

        return $table;
    }

    /**
     * @param mixed string|ItemId|PropertyId|DataValues\DataValue $dataValue
     * @return string
     */
    private function formatValue( $dataValue ) {
        if( is_string( $dataValue ) ) { // cases like 'Format' 'pattern' or 'minimum'/'maximum' values, which we have stored as strings
            return ( $dataValue );
        } else if( get_class( $dataValue ) === 'Wikibase\DataModel\Entity\ItemId' || get_class( $dataValue ) === 'Wikibase\DataModel\Entity\PropertyId' ) { // cases like 'Conflicts with' 'property', to which we can link
            return $this->entityIdHtmlLinkFormatter->formatEntityId( $dataValue );
        } else { // cases where we format a DataValue
            if ( $dataValue->getType() === 'wikibase-entityid' ) { // Entities, to which we can link
                return $this->entityIdHtmlLinkFormatter->formatEntityId( $dataValue->getEntityId() );
            } else { // other DataValues, which can be formatted
                return $this->dataValueFormatter->format( $dataValue );
            }
        }
    }

    /**
     * @param mixed string|ItemId|PropertyId|DataValues\DataValue $dataValue
     * @return string
     */
    private function formatValueForTooltip( $dataValue ) {
        if( is_string( $dataValue ) ) { // cases like 'Format' 'pattern' or 'minimum'/'maximum' values, which we have stored as strings
            return ( $dataValue );
        } else if( get_class( $dataValue ) === 'Wikibase\DataModel\Entity\ItemId' || get_class( $dataValue ) === 'Wikibase\DataModel\Entity\PropertyId' ) { // cases like 'Conflicts with' 'property', to which we can link
            return $this->entityIdLabelFormatter->formatEntityId( $dataValue );
        } else { // other DataValues, which can be formatted
            return $this->dataValueFormatter->format( $dataValue );
        }
    }

    /**
     * @param array $parameters
     * @return string
     */
    private function formatParameters( $parameters ) {
        $formattedParameters = '';
        $parameterNames = array_keys( $parameters );

        foreach( $parameterNames as $parameterName ) {
            $formattedParameters .= ( $parameterName . ': ' );
            $parameterValue = $parameters[$parameterName];

            $formattedParameters .= implode( ', ', $this->limitArrayLength( array_map( array( 'self', 'formatValueForTooltip' ), $parameterValue ) ) );

            if( $parameterName !== end( $parameterNames ) ) {
                $formattedParameters .= '; ';
            }
        }

        return $formattedParameters;
    }

    private function limitArrayLength( $array ) {
        if( count( $array ) > $this->maxParameterArrayLength ) {
            $array = array_slice( $array, 0, $this->maxParameterArrayLength );
            array_push( $array, '...' );
        }
        return $array;
    }

}