<?php

namespace WikidataQuality\ExternalValidation\Specials;

use SpecialPage;

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
    function getGroupName() {
        return "wikidataquality";
    }

    /**
     * @see SpecialPage::getDescription
     *
     * @return string
     */
    public function getDescription() {
        return $this->msg( 'special-crosscheck' )->text();
    }

    /**
     * @see SpecialPage::execute
     *
     * @param string|null $subPage
     */
    function execute( $subPage )
    {
    }
}