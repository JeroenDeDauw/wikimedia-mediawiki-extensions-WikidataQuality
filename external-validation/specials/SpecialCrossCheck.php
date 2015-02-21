<?php

namespace WikidataQuality\ExternalValidation\Specials;

use SpecialPage;
use Html;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use WikidataQuality\ExternalValidation\CrossCheck\CrossChecker;


class SpecialCrossCheck extends SpecialPage
{
    /**
     * Wikibase entity lookup.
     * @var \Wikibase\Lib\Store\EntityLookup
     */
    private $entityLookup;


    private $entityIdParser;


    function __construct()
    {
        parent::__construct( 'CrossCheck' );

        // Get entity lookup
        $this->entityLookup = WikibaseRepo::getDefaultInstance()->getEntityLookup();

        // Get entity id parser
        $this->entityIdParser = new BasicEntityIdParser();
    }


    /**
     * @see SpecialPage::getGroupName
     *
     * @return string
     */
    function getGroupName()
    {
        return 'wikidataquality';
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
                . $this->msg( 'wikidataquality-crosscheck-result-headline' )->text() . $_POST[ 'entityId' ]
                . Html::closeElement( 'h3' )
            );

            if ( $results ) {
                // Head of table
                $tableOutput =
                    "{| class=\"wikitable sortable\"\n"
                    . '! '. $this->msg( 'datatypes-type-wikibase-property' )->text() ." !! class=\"unsortable\" | ". $this->msg( 'wikidataquality-value' )->text() ." !! class=\"unsortable\" | ". $this->msg( 'wikidataquality-crosscheck-comparative-value' )->text() ." !! ". $this->msg( 'wikidataquality-crosscheck-external-source' )->text() ." !! ". $this->msg( 'wikidataquality-status' )->text() ."\n";

                foreach ( $results as $result ) {
                    // Parse value arrays to concatenated strings
                    $localValues = $this->parseMultipleValues(
                        $result->getLocalValues(),
                        $this->msg( 'wikidataquality-crosscheck-result-no-wd-entity' )->text()
                    );
                    $externalValues = $this->parseMultipleValues(
                        $result->getExternalValues(),
                        $this->msg( 'wikidataquality-crosscheck-result-no-ext-entity' )->text()
                    );

                    if ( $result->hasDataMismatchOccurred() ) {
                        $status = "| <span class=\"wdq-crosscheck-error\"> ". $this->msg( 'wikidataquality-crosscheck-result-mismatch' )->text() ." <b>(-)</b></span>\n";
                    } else {
                        $status = "| <span class=\"wdq-crosscheck-success\">". $this->msg( 'wikidataquality-crosscheck-result-success' )->text() ." <b>(+)</b></span>\n";
                    }

                    // Body of table
                    $tableOutput .=
                        "|-\n"
                        . '| ' . $result->getPropertyId() . "\n"
                        . '| ' . $localValues . "\n"
                        . '| ' . $externalValues . "\n"
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
     * Parse arary of values to human-readable string
     * @param $values
     * @param $errorMessage
     * @return string
     */
    private function parseMultipleValues( $values, $errorMessage )
    {
        if ( $values ) {
            return implode( ', ', $values );
        } else {
            return $errorMessage;
        }
    }
}