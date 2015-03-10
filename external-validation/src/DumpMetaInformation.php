<?php

namespace WikidataQuality\ExternalValidation;


use DateTime;
use Wikibase\DataModel\Entity\ItemId;


/**
 * Class DumpMetaInformation
 * @package WikidataQuality\ExternalValidation\CrossCheck
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class DumpMetaInformation
{
    /**
     * Id of dump meta information in database.
     * @var int
     */
    private $dumpId;

    /**
     * Id of the item that represents the data source of the dump.
     * @var ItemId
     */
    private $sourceItemId;

    /**
     * Date of import.
     * @var DateTime
     */
    private $importDate;

    /**
     * Data format of the dump.
     * @var string
     */
    private $format;

    /**
     * Language of data in the dump.
     * @var string
     */
    private $language;

    /**
     * Source url of the downloaded dump.
     * @var string
     */
    private $sourceUrl;

    /**
     * Size of the imported dump.
     * @var int
     */
    private $size;

    /**
     * License of the database.
     * @var string
     */
    private $license;


    /**
     * @param $sourceItemId
     * @param $importDate
     * @param $format
     * @param $language
     * @param $sourceUrl
     * @param $size
     * @param $license
     */
    public function __construct( $sourceItemId, $importDate, $format, $language, $sourceUrl, $size, $license )
    {
        $this->sourceItemId = $sourceItemId;
        $this->importDate = $importDate;
        $this->format = $format;
        $this->language = $language;
        $this->sourceUrl = $sourceUrl;
        $this->size = $size;
        $this->license = $license;
    }


    /**
     * Returns id of the item that represents the data source of the dump.
     * @return ItemId
     */
    public function getSourceItemId()
    {
        return $this->sourceItemId;
    }

    /**
     * Returns date of import.
     * @return DateTime
     */
    public function getImportDate()
    {
        return $this->importDate;
    }

    /**
     * Returns data format.
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Returns language of dump.
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Returns url of imported dump.
     * @return string
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }

    /**
     * Returns size of imported dump.
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Returns license of database.
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }


    /**
     * Saves dump meta information to database.
     * @param $db
     */
    public function save( $db )
    {
        // Set accumulator
        $accumulator = array(
            'source_item_id' => $this->getSourceItemId()->getNumericId(),
            'format' => $this->getFormat(),
            'language' => $this->getLanguage(),
            'source_url' => $this->getSourceUrl(),
            'size' => $this->getSize(),
            'license' => $this->getLicense()
        );

        // Check, whether to create new row or update existing one
        $existing = false;
        if ( isset( $this->dumpId ) ) {
            $dumpId = $this->dumpId;
            $existing = $db->selectRow(
                DUMP_META_TABLE,
                array(),
                array( "row_id=$dumpId" )
            );
        }

        // Perform database operation
        if ( $existing ) {
            // Update existing row
            $result = $db->update(
                DUMP_META_TABLE,
                $accumulator,
                array( "row_id=$dumpId" )
            );
        } else {
            // Insert new row
            $result = $db->insert(
                DUMP_META_TABLE,
                $accumulator
            );
        }

        return $result;
    }

    /**
     * Gets DumpMetaInformation for specific dump id from database.
     * @param $db
     * @param $dumpId
     * @return null|DumpMetaInformation
     */
    public static function get( $db, $dumpId )
    {
        // Run query
        $result = $db->selectRow(
            DUMP_META_TABLE,
            array( 'source_item_id', 'import_date', 'format', 'language', 'source_url', 'size', 'license' ),
            array( "row_id=$dumpId" ) );

        if ( $result != false ) {
            $dataSource = new ItemId( 'Q' . $result->source_item_id );
            $import_date = new DateTime( $result->import_date );
            $format = $result->format;
            $language = $result->language;
            $sourceUrl = $result->source_url;
            $size = (int)$result->size;
            $license = $result->license;

            return new DumpMetaInformation( $dataSource, $import_date, $format, $language, $sourceUrl, $size, $license );
        }
    }
}