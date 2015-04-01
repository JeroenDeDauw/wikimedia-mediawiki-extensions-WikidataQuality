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
                $this->msg( 'wikidataquality-externaldbs-name' )->text(),
                $this->msg( 'wikidataquality-externaldbs-import-date' )->text(),
                $this->msg( 'wikidataquality-externaldbs-language' )->text(),
                $this->msg( 'wikidataquality-externaldbs-source-urls' )->text(),
                $this->msg( 'wikidataquality-externaldbs-size' )->text(),
                $this->msg( 'wikidataquality-externaldbs-license' )->text()
            ),
            true
        );

        wfWaitForSlaves();
        $loadBalancer = wfGetLB();
        $db = $loadBalancer->getConnection( DB_SLAVE );
        $dumps = DumpMetaInformation::get( $db );

        if ( $dumps ) {
            foreach ( $dumps as $dumpMetaInformation  ) {
                $table->appendRow(
                    array(
                        $this->entityIdHtmlLinkFormatter->formatEntityId( $dumpMetaInformation->getSourceItemId() ),
                        $this->formatDateTime( $dumpMetaInformation->getImportDate() ),
                        Language::fetchLanguageName(
                            $dumpMetaInformation->getLanguage(),
                            $this->getLanguage()->getCode()
                        ),
                        $this->formatSourceUrls(
                            $dumpMetaInformation->getSourceUrls(),
                            Html::element( 'br' )
                        ),
                        $this->getLanguage()->formatSize( $dumpMetaInformation->getSize() ),
                        $dumpMetaInformation->getLicense()
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

    /**
     * Formats given array of urls to links, optionally separated by given string
     * @param array $sourceUrls
     * @return string
     */
    private function formatSourceUrls( $sourceUrls, $separator = '' )
    {
        $urlFormatter = function( $url )
        {
            return
                Html::element(
                    'a',
                    array( 'class' => 'external free', 'href' => $url ),
                    $url
                );
        };
        $sourceUrlLinks = array_map( $urlFormatter, $sourceUrls );
        $output = implode( $separator, $sourceUrlLinks );

        return $output;
    }
}