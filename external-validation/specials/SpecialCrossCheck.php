<?php

namespace WikidataQuality\ExternalValidation\Specials;

use SpecialPage;
use Html;
use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ExternalValidation\CrossCheck\CrossChecker;

class SpecialCrossCheck extends SpecialPage
{
    function __construct()
    {
        parent::__construct( 'CrossCheck' );
    }

    /**
     * @see SpecialPage::getGroupName
     *
     * @return string
     */
    function getGroupName()
    {
        return "wikidataquality";
    }

    /**
     * @see SpecialPage::getDescription
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->msg( 'special-crosscheck' )->text();
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
            . $this->msg( 'special-crosscheck-instructions' )->text()
            . Html::element( 'br' )
            . $this->msg( 'special-crosscheck-instructions-example' )->text()
            . Html::closeElement( 'p' )
            . Html::openElement(
                'form',
                array(
                    'action' => $_SERVER[ 'PHP_SELF' ],
                    'method' => 'post'
                )
            )
            . Html::input(
                'itemId',
                '',
                'text',
                array(
                    'id' => 'wdq-crosscheck-itemid',
                    'placeholder' => $this->msg( 'special-crosscheck-form-itemid-placeholder' )->text()
                )
            )
            . Html::input(
                'submit',
                $this->msg( 'special-crosscheck-form-submit-label' )->text(),
                'submit',
                array(
                    'id' => 'wbq-crosscheck-submit'
                )
            )
            . Html::closeElement( 'form' )
        );

        // If item id was recieved, cross-check item
        if ( !empty( $_POST[ 'itemId' ] ) ) {
            $itemId = new ItemId( $_POST[ 'itemId' ] );
            $crossChecker = new CrossChecker();
            $results = $crossChecker->execute( $itemId );

            // Print results
            $out->addHTML(
                Html::openElement( 'h3' )
                . $this->msg( 'special-crosscheck-result-headline' )->text() . $_POST[ 'itemId' ]
                . Html::closeElement( 'h3' )
            );

            if ( $results ) {
                $out->addHTML( Html::openElement( 'ul' ) );
                foreach ( $results as $result ) {
                    // Parse value arrays to concatenated strings
                    $localValues = $this->parseMultipleValues(
                        $result->getLocalValues(),
                        $this->msg( 'special-crosscheck-result-no-wd-entity' )->text()
                    );
                    $externalValues = $this->parseMultipleValues(
                        $result->getExternalValues(),
                        $this->msg( 'special-crosscheck-result-no-ext-entity' )->text()
                    );

                    // Print list item
                    if ( $result->hasDataMismatchOccurred() ) {
                        $out->addHTML(
                            Html::openElement(
                                'li',
                                array(
                                    'class' => 'wdq-crosscheck-mismatch'
                                )
                            )
                            . $result->getPropertyId()
                            . $this->msg( 'special-crosscheck-result-mismatch' )->text()
                            . Html::element( 'br' )
                            . $localValues
                            . ' &harr; '
                            . $externalValues
                            . ' ('
                            . $result->getDataSourceName()
                            . ')'
                            . Html::closeElement( 'li' )
                        );
                    } else {
                        $out->addHTML(
                            Html::openElement(
                                'li',
                                array(
                                    'class' => 'wdq-crosscheck-success'
                                )
                            )
                            . $result->getPropertyId()
                            . $this->msg( 'special-crosscheck-result-success' )->text()
                            . Html::element( 'br' )
                            . $localValues
                            . ' &harr; '
                            . $externalValues
                            . Html::closeElement( 'li' )
                        );
                    }
                }
                $out->addHTML(
                    Html::closeElement( 'ul' )
                );
            }
            else {
                $out->addHTML(
                    Html::openElement(
                        'p',
                        array(
                            'class' => 'wdq-crosscheck-error'
                        )
                    )
                    . $this->msg( 'special-crosscheck-result-item-not-existent' )->text()
                    . Html::closeElement( 'p ')
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
    private function parseMultipleValues( $values, $errorMessage ) {
        if( $values ) {
            return implode( ', ', $values );
        }
        else {
            return $errorMessage;
        }
    }
}