<?php

namespace WikidataQuality\ExternalValidation\Specials;


use Html;
use Language;
use DateTime;
use DateTimeZone;
use DateInterval;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use WikidataQuality\Html\HtmlTable;
use WikidataQuality\Specials\SpecialWikidataQualityPage;


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

        // Build external dbs form
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
                $this->msg( 'wikidataquality-externaldbs-language' )->text(),
                $this->msg( 'wikidataquality-externaldbs-source-url' )->text(),
                $this->msg( 'wikidataquality-externaldbs-size' )->text(),
                $this->msg( 'wikidataquality-externaldbs-license' )->text()
            ),
            true
        );

        wfWaitForSlaves();
        $loadBalancer = wfGetLB();
        $db = $loadBalancer->getConnection( DB_SLAVE );

        $resultDbIds = $db->select(
            DUMP_META_TABLE,
            array( 'row_id' ) );

        if ( $resultDbIds ) {
            foreach ( $resultDbIds as $row ) {
                $dbId = $row->row_id;
                $metaInformation = DumpMetaInformation::get( $db, $dbId );

                $table->appendRow(
                    array(
                        $this->entityIdHtmlLinkFormatter->formatEntityId( $metaInformation->getSourceItemId() ),
                        $this->formatDateTime( $metaInformation->getImportDate() ),
                        Language::fetchLanguageName(
                            $metaInformation->getLanguage(),
                            $this->getLanguage()->getCode()
                        ),
                        Html::element(
                            'a',
                            array( 'class' => 'external free', 'href' => $metaInformation->getSourceUrl() ),
                            $metaInformation->getSourceUrl()
                        ),
                        $this->getLanguage()->formatSize( $metaInformation->getSize() ),
                        $metaInformation->getLicense()
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

    /**
     * Formats given date time to string depending on user preferences.
     * @param DateTiem $dateTime
     * @return string
     */
    private function formatDateTime( $dateTime )
    {
        global $wgLocaltimezone;

        // Apply time correction
        $timeCorrection = $this->getUser()->getOption( 'timecorrection' );
        if ( $timeCorrection ) {
            $splitTimeCorrection = explode( '|', $timeCorrection );
            switch( $splitTimeCorrection[ 0 ] )
            {
                case 'System':
                case 'Offset':
                    $offset = $splitTimeCorrection[ 1 ];
                    $interval = new DateInterval( sprintf( 'PT%dM', $offset ) );
                    $dateTime->sub( $interval );
                    break;

                case 'TimeZone':
                    $timeZone = new DateTimeZone( $splitTimeCorrection[ 2 ] );
                    $dateTime->setTimezone( $timeZone );
                    break;
            }
        }
        else
        {
            $dateTime->setTimezone( new DateTimeZone( $wgLocaltimezone ) );
        }

        // Get date format
        $dateFormatPreference = $this->getUser()->getDatePreference();
        switch( $dateFormatPreference )
        {
            case 'mdy';
                $dateFormat = 'H:i, F d, Y';
                break;

            case 'dmy':
                $dateFormat = 'H:i, d F, Y';
                break;

            case 'ymd':
                $dateFormat = 'H:i, Y F d';
                break;

            case 'ISO 8601':
                $dateFormat = 'Y-m-d\TH:i:s';
                break;

            default:
            case 'default':
                $dateFormat = 'Y-m-d H:i:s';
                break;
        }

        return $dateTime->format( $dateFormat );
    }
}