<?php

namespace WikidataQuality\ExternalValidation\Specials;


use Html;
use Wikibase\DataModel\Entity\EntityIdValue;
use WikidataQuality\ExternalValidation\CrossCheck\CrossChecker;
use WikidataQuality\Specials\SpecialWikidataQualityPage;


class SpecialCrossCheck extends SpecialWikidataQualityPage
{
    function __construct()
    {
        parent::__construct( 'CrossCheck' );
    }


    /**
     * @see SpecialPage::getDescription
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->msg( 'wikidataquality-crosscheck' )->text();
    }

    /**
     * @see SpecialPage::execute
     *
     * @param string|null $subPage
     */
    function execute( $subPage )
    {
        // Get output
        $out = $this->getOutput();

        // Include modules
        $out->addModuleStyles( 'SpecialCrossCheck' );

        // Build cross-check form
        $this->setHeaders();

        $out->addHTML(
            Html::openElement( 'p' )
            . $this->msg( 'wikidataquality-crosscheck-instructions' )->text()
            . Html::element( 'br' )
            . $this->msg( 'wikidataquality-crosscheck-instructions-example' )->text()
            . Html::closeElement( 'p' )
            . Html::openElement(
                'form',
                array(
                    'action' => $_SERVER[ 'PHP_SELF' ],
                    'method' => 'post'
                )
            )
            . Html::input(
                'entityId',
                '',
                'text',
                array(
                    'id' => 'wdq-crosscheck-entityid',
                    'placeholder' => $this->msg( 'wikidataquality-crosscheck-form-entityid-placeholder' )->text()
                )
            )
            . Html::input(
                'submit',
                $this->msg( 'wikidataquality-crosscheck-form-submit-label' )->text(),
                'submit',
                array(
                    'id' => 'wbq-crosscheck-submit'
                )
            )
            . Html::closeElement( 'form' )
        );

        // If entity id id was recieved, cross-check entity
        if ( !empty( $_POST[ 'entityId' ] ) ) {
            $entityId = $this->entityIdParser->parse( $_POST[ 'entityId' ] );
            $entity = $this->entityLookup->getEntity( $entityId );
            $crossChecker = new CrossChecker();
            $results = $crossChecker->crossCheckEntity( $entity );

            // Print results
            $out->addHTML(
                Html::openElement( 'h3' )
                . $this->msg( 'wikidataquality-crosscheck-result-headline' )->text()
                . $this->entityIdHtmlLinkFormatter->format( $this->entityIdParser->parse( $_POST[ 'entityId' ] ) )
                . Html::closeElement( 'h3' )
            );

            if ( $results ) {
                // Head of table
                $tableOutput =
                    "{| class=\"wikitable sortable\"\n"
                    . '! ' . $this->msg( 'datatypes-type-wikibase-property' )->text() . " !! class=\"unsortable\" | " . $this->msg( 'wikidataquality-value' )->text() . " !! class=\"unsortable\" | " . $this->msg( 'wikidataquality-crosscheck-comparative-value' )->text() . " !! " . $this->msg( 'wikidataquality-crosscheck-external-source' )->text() . " !! " . $this->msg( 'wikidataquality-status' )->text() . "\n";

                foreach ( $results as $result ) {
                    if ( $result->hasDataMismatchOccurred() ) {
                        $status = "| <span class=\"wdq-crosscheck-error\"> " . $this->msg( 'wikidataquality-crosscheck-result-mismatch' )->text() . " <b>(-)</b></span>\n";
                    } else {
                        $status = "| <span class=\"wdq-crosscheck-success\">" . $this->msg( 'wikidataquality-crosscheck-result-success' )->text() . " <b>(+)</b></span>\n";
                    }

                    // Body of table
                    $tableOutput .=
                        "|-\n"
                        . '| ' . $this->entityIdLinkFormatter->format( $result->getPropertyId() ) . "\n"
                        . '| ' . $this->formatDataValues( $result->getLocalValue() ) . "\n"
                        . '| ' . $this->formatDataValues( $result->getExternalValues() ) . "\n"
                        . '| ' . $result->getDataSourceName() . "\n"
                        . $status;
                }

                // End of table
                $tableOutput .= "|-\n|}";
                $out->addWikiText( $tableOutput );
            } else {
                $out->addHTML(
                    Html::openElement(
                        'p',
                        array(
                            'class' => 'wdq-crosscheck-error'
                        )
                    )
                    . $this->msg( 'wikidataquality-crosscheck-result-item-not-existent' )->text()
                    . Html::closeElement( 'p ' )
                );
            }
        }
    }


    /**
     * Parses data values to human-readable string
     * @param DataValue|array $dataValues
     * @return string
     */
    private function formatDataValues( $dataValues )
    {
        if ( !is_array( $dataValues ) ) {
            $dataValues = array( $dataValues );
        }

        $formattedDataValues = array();
        foreach ( $dataValues as $dataValue ) {
            if ( $dataValue instanceof EntityIdValue ) {
                $formattedDataValues[ ] = $this->entityIdLinkFormatter->format( $dataValue );
            } else {
                $formattedDataValues[ ] = $this->dataValueFormatter->format( $dataValue );
            }
        }

        return implode( ', ', $formattedDataValues );
    }
}