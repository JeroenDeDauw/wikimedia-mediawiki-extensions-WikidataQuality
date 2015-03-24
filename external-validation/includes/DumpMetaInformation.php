<?php

namespace WikidataQuality\ExternalValidation;

use DateTime;
use DateTimeZone;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
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
     * @param $dumpId
     * @param $sourceItemId
     * @param $importDate
     * @param $language
     * @param $sourceUrl
     * @param $size
     * @param $license
     * @throws InvalidArgumentException
     */
    public function __construct( $dumpId, $sourceItemId, $importDate, $language, $sourceUrl, $size, $license )
    {
        $this->dumpId = $dumpId;
        if ( is_string( $sourceItemId ) ) {
            if ( $sourceItemId[ 0 ] !== 'Q' ) {
                $sourceItemId = 'Q' . $sourceItemId;
            }
            $this->sourceItemId = new ItemId( $sourceItemId );
        } elseif ( $sourceItemId instanceof ItemId ) {
            $this->sourceItemId = $sourceItemId;
        } else {
            throw new InvalidArgumentException( '$sourceItemId must be either string or instance of ItemId.' );
        }

        if ( $importDate instanceof DateTime ) {
            $this->importDate = $importDate;
        } else {
            throw new InvalidArgumentException( '$importDate must be an instance of DateTime.' );
        }

        $this->language = $language;
        $this->sourceUrl = $sourceUrl;
        $this->size = $size;
        $this->license = $license;
    }

    /**
     * Returns id of dump meta information in database.
     * @return string
     */
    public function getDumpId()
    {
        return $this->dumpId;
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
            'dump_id' => $this->getDumpId(),
            'source_item_id' => $this->getSourceItemId()->getNumericId(),
            'import_date' => $this->getImportDate()->format( DateTime::ISO8601 ),
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
                array( 'dump_id' ),
                array( "dump_id=$dumpId" )
            );
        }

        // Perform database operation
        if ( $existing ) {
            // Update existing row
            $result = $db->update(
                DUMP_META_TABLE,
                $accumulator,
                array( "dump_id=$dumpId" )
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
     * Gets DumpMetaInformation for specific dump ids from database.
     * @param DatabaseBase $db
     * @param string|array $dumpIds
     * @return array|DumpMetaInformation
     */
    public static function get( $db, $dumpIds = null )
    {
        // Check arguments
        if ( $dumpIds ) {
            if ( is_string( $dumpIds ) ) {
                $dumpIds = array( $dumpIds );
            } elseif ( !is_array( $dumpIds ) ) {
                throw new InvalidArgumentException( '$dumpIds must be array of strings.' );
            }
        }

        // Build condition
        $conditions = array();
        if ( $dumpIds ) {
            $conditions[ ] = sprintf( 'dump_id IN (%s)', implode( ',', $dumpIds ) );
        }

        // Run query
        $result = $db->select(
            DUMP_META_TABLE,
            array( 'dump_id', 'source_item_id', 'import_date', 'language', 'source_url', 'size', 'license' ),
            $conditions
        );

        // Create DumpMetaInformation instances
        $dumpMetaInformation = array();
        foreach ( $result as $row ) {
            $dumpId = $row->dump_id;
            $dataSource = new ItemId( 'Q' . $row->source_item_id );
            $import_date = new DateTime( $row->import_date, new DateTimeZone( 'UTC' ) );
            $language = $row->language;
            $sourceUrl = $row->source_url;
            $size = (int)$row->size;
            $license = $row->license;

            $dumpMetaInformation[ $dumpId ] = new DumpMetaInformation( $dumpId, $dataSource, $import_date, $language, $sourceUrl, $size, $license );
        }

        if( count( $dumpMetaInformation ) > 0 ) {
            if ( $dumpIds && count( $dumpIds ) == 1 ) {
                $dumpMetaInformation = array_values( $dumpMetaInformation );

                return $dumpMetaInformation[ 0 ];
            } else {
                return $dumpMetaInformation;
            }
        }
    }
}