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
        // Build cross-check form
        $this->setHeaders();
        $this->getOutput()->addHTML(
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
        if ( isset( $_POST[ 'itemId' ] ) ) {
            $itemId = new ItemId( $_POST[ 'itemId' ] );

            $crossChecker = new CrossChecker();
            $results = $crossChecker->execute( $itemId );
        }
    }
}