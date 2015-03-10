<?php

namespace WikidataQuality\ExternalValidation\Specials;


use Html;
use WikidataQuality\Html\HtmlTable;
use WikidataQuality\Specials\SpecialWikidataQualityPage;
use WikidataQuality\ExternalValidation\DumpMetaInformation;


class SpecialExternalDbs extends SpecialWikidataQualityPage
{
    function __construct()
    {
        parent::__construct( 'ExternalDbs' );
    }


    /**
     * @see SpecialPage::getDescription
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->msg( 'wikidataquality-externaldbs' )->text();
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

        // Build externaldbs form
        $this->setHeaders();

        $out->addHTML(
            Html::openElement( 'p' )
            . $this->msg( 'wikidataquality-externaldbs-instructions' )->text()
            . Html::closeElement( 'p' )
            . Html::openElement( 'h3' )
            . $this->msg( 'wikidataquality-externaldbs-overview-headline' )->text()
            . Html::closeElement( 'h3' )
        );

        $table = new HtmlTable(
            array(
                $this->msg( 'wikidataquality-externaldbs-source-item-id' )->text(),
                $this->msg( 'wikidataquality-externaldbs-import-date' )->text(),
                $this->msg( 'wikidataquality-externaldbs-format' )->text(),
                $this->msg( 'wikidataquality-externaldbs-language' )->text(),
                $this->msg( 'wikidataquality-externaldbs-source-url' )->text(),
                $this->msg( 'wikidataquality-externaldbs-size' )->text(),
                $this->msg( 'wikidataquality-externaldbs-license' )->text()
            ),
            true
        );

        wfWaitForSlaves();
        $loadBalancer = wfGetLB();
        $db_connection = $loadBalancer->getConnection( DB_SLAVE );

        $result_db_ids = $db_connection->select(
            DUMP_META_TABLE,
            array( 'row_id' ) );

        if ( $result_db_ids ){
            foreach ( $result_db_ids as $row ) {
                $db_id = $row->row_id;

                $db_meta_information = DumpMetaInformation::get($db_connection, $db_id);

                $table->appendRow(
                    array(
                        $this->entityIdHtmlLinkFormatter->formatEntityId($db_meta_information->getSourceItemId()),
                        $db_meta_information->getImportDate()->format('Y-m-d H:i:s'),
                        $db_meta_information->getFormat(),
                        $db_meta_information->getLanguage(),
                        $db_meta_information->getSourceUrl(),
                        $db_meta_information->getSize(),
                        $db_meta_information->getLicense()
                    )
                );
            }

            $out->addHTML( $table->toHtml() );
        } else {
            $out->addHTML(
                Html::openElement( 'p' )
                . $this->msg( 'wikidataquality-externaldbs-no-databases' )->text()
                . Html::closeElement( 'p' )
            );
        }
    }
}