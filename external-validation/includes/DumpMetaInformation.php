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
     * @param $sourceItemId
     * @param $importDate
     * @param $language
     * @param $sourceUrl
     * @param $size
     * @param $license
     * @throws InvalidArgumentException
     */
    public function __construct( $sourceItemId, $importDate, $language, $sourceUrl, $size, $license )
    {
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
            'source_item_id' => $this->getSourceItemId()->getNumericId(),
            'import_date' => $this->getImportDate()->format( DateTime::ISO8601 ),
            'language' => $this->getLanguage(),
            'source_url' => $this->getSourceUrl(),
            'size' => $this->getSize(),
            'license' => $this->getLicense()
        );

        // Check, whether to create new row or update existing one
        $sourceItemId = $this->getSourceItemId()->getNumericId();
        $existing = $db->selectRow(
            DUMP_META_TABLE,
            array( 'source_item_id' ),
            array( "source_item_id=$sourceItemId" )
        );

        // Perform database operation
        if ( $existing ) {
            // Update existing row
            $result = $db->update(
                DUMP_META_TABLE,
                $accumulator,
                array( "source_item_id=$sourceItemId" )
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
     * @param string|array $sourceItemIds
     * @return array|DumpMetaInformation
     */
    public static function get( $db, $sourceItemIds = null )
    {
        // Check arguments
        if ( $sourceItemIds ) {
            if (  $sourceItemIds instanceof ItemId ) {
                $sourceItemIds = array( $sourceItemIds );
            } elseif ( !is_array( $sourceItemIds ) ) {
                throw new InvalidArgumentException( '$sourceItemIds must be array of ItemIds.' );
            }
            else {
                foreach( $sourceItemIds as $sourceItemId )
                {
                    if ( !$sourceItemId instanceof ItemId )
                    {
                        throw new InvalidArgumentException( '$sourceItemIds must be array of ItemIds.' );
                    }
                }
            }
        }

        // Build condition
        $conditions = array();
        $mapFunction = function( $itemId )
        {
            return $itemId->getNumericId();
        };
        if ( $sourceItemIds ) {
            $conditions[ ] = sprintf( 'source_item_id IN (%s)', implode( ',', array_map( $mapFunction, $sourceItemIds ) ) );
        }

        // Run query
        $result = $db->select(
            DUMP_META_TABLE,
            array( 'source_item_id', 'import_date', 'language', 'source_url', 'size', 'license' ),
            $conditions
        );

        // Create DumpMetaInformation instances
        $dumpMetaInformation = array();
        foreach ( $result as $row ) {
            $sourceItemId = new ItemId( 'Q' . $row->source_item_id );
            $import_date = new DateTime( $row->import_date, new DateTimeZone( 'UTC' ) );
            $language = $row->language;
            $sourceUrl = $row->source_url;
            $size = (int)$row->size;
            $license = $row->license;

            $dumpMetaInformation[ $sourceItemId->getNumericId() ] = new DumpMetaInformation( $sourceItemId, $import_date, $language, $sourceUrl, $size, $license );
        }

        if ( count( $dumpMetaInformation ) > 0 ) {
            if ( $sourceItemIds && count( $sourceItemIds ) == 1 ) {
                $dumpMetaInformation = array_values( $dumpMetaInformation );

                return $dumpMetaInformation[ 0 ];
            } else {
                return $dumpMetaInformation;
            }
        }
    }
}