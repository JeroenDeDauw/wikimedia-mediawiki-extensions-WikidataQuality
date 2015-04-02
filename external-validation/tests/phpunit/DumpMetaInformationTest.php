<?php

namespace WikidataQuality\ExternalValidation\Tests\DumpMetaInformation;

use Wikibase\DataModel\Entity\Item;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use Wikibase\DataModel\Entity\ItemId;
use DateTime;

/**
 * @covers WikidataQuality\ExternalValidation\DumpMetaInformation
 *
 * @group Database
 *
 * @author BP2014N1
 * @license GNU GPL v2+exte
 */
class DumpMetaInformationTest extends \MediaWikiTestCase
{
    /**
     * @var array
     */
    private $dumpMetaInformation  = array();


    public function __construct( $name = null, $data = array(), $dataName = null )
    {
        parent::__construct( $name, $data, $dataName );

        // Create example dump meta information
        $this->dumpMetaInformation[ 1 ] = new DumpMetaInformation(
            new ItemId( 'Q1' ),
            new DateTime( '2015-01-01 00:00:00' ),
            'en',
            'http://www.foo.bar',
            42,
            'CC0'
        );
        $this->dumpMetaInformation[ 2 ] = new DumpMetaInformation(
            new ItemId( 'Q2' ),
            new DateTime( '2020-01-01 12:12:12' ),
            'de',
            'http://www.fu.bar',
            4242,
            'CC0'
        );
    }


    public function setUp()
    {
        parent::setUp();

        // Specify database table used by this test
        $this->tablesUsed[ ] = DUMP_META_TABLE;
    }

    /**
     * Adds temporary test data to database
     * @throws \DBUnexpectedError
     */
    public function addDBData()
    {
        // Truncate tables
        $this->db->delete(
            DUMP_META_TABLE,
            '*'
        );

        // Insert example dump meta information
        foreach ( $this->dumpMetaInformation as $dumpMetaInformation ) {
            $this->db->insert(
                DUMP_META_TABLE,
                array(
                    'source_item_id' => $dumpMetaInformation->getSourceItemId()->getNumericId(),
                    'import_date' => $dumpMetaInformation->getImportDate()->format( DateTime::ISO8601 ),
                    'language' => $dumpMetaInformation->getLanguage(),
                    'source_url' => json_encode( $dumpMetaInformation->getSourceUrls() ),
                    'size' => $dumpMetaInformation->getSize(),
                    'license' =>  $dumpMetaInformation->getLicense()
                )
            );
        }
    }


    /**
     * @dataProvider constructValidArgumentsDataProvider
     */
    public function testConstructValidArguments( $sourceItemId, $importDate, $language, $sourceUrl, $size, $license, $expectedSourceItemId )
    {
        $metaInformation = new DumpMetaInformation( $sourceItemId, $importDate, $language, $sourceUrl, $size, $license );

        if( !is_array( $sourceUrl ) ) {
            $sourceUrl = array( $sourceUrl );
        }

        $this->assertEquals( $expectedSourceItemId, $metaInformation->getSourceItemId() );
        $this->assertEquals( $importDate, $metaInformation->getImportDate() );
        $this->assertEquals( $language, $metaInformation->getLanguage() );
        $this->assertEquals( $sourceUrl, $metaInformation->getSourceUrls() );
        $this->assertEquals( $size, $metaInformation->getSize() );
        $this->assertEquals( $license, $metaInformation->getLicense() );
    }

    /**
     * Test cases for testConstructValidArguments
     * @return array
     */
    public function constructValidArgumentsDataProvider()
    {
        $itemId = new ItemId( 'Q123' );
        $importDate = new DateTime( '30-11-2015' );
        $language = 'de';
        $sourceUrl = 'http://randomurl.tld';
        $size = 42;
        $license = 'CC0';

        return array(
            array(
                $itemId,
                $importDate,
                $language,
                $sourceUrl,
                $size,
                $license,
                $itemId
            ),
            array(
                $itemId->getSerialization(),
                $importDate,
                $language,
                $sourceUrl,
                $size,
                $license,
                $itemId
            ),
            array(
                (string)$itemId->getNumericId(),
                $importDate,
                $language,
                $sourceUrl,
                $size,
                $license,
                $itemId
            ),
            array(
                (string)$itemId->getNumericId(),
                $importDate,
                $language,
                array( $sourceUrl ),
                $size,
                $license,
                $itemId
            )
        );
    }


    /**
     * @dataProvider constructInvalidArgumentsDataProvider
     */
    public function testConstructInvalidArguments( $sourceItemId, $importDate, $language, $sourceUrl, $size, $license )
    {
        $this->setExpectedException( 'InvalidArgumentException' );

        new DumpMetaInformation( $sourceItemId, $importDate, $language, $sourceUrl, $size, $license );
    }

    /**
     * Test cases for testConstructInvalidArguments
     * @return array
     */
    public function constructInvalidArgumentsDataProvider()
    {
        $importDate = new DateTime( '2015-03-17' );
        $language = 'de';
        $sourceUrl = 'http://randomurl.tld';
        $size = 42;
        $license = 'CC0';

        return array(
            array(
                123,
                $importDate,
                $language,
                $sourceUrl,
                $size,
                $license
            ),
            array(
                new ItemId( 'Q123' ),
                '2015-03-17',
                $language,
                $sourceUrl,
                $size,
                $license
            ),
            array(
                new ItemId( 'Q123' ),
                $importDate,
                $language,
                42,
                $size,
                $license
            )
        );
    }


    /**
     * @dataProvider getDumpMetaInformationDataProvider
     */
    public function testGetDumpMetaInformation( $dataSourceIds, $expectedDumpMetaInformation, $expectedException )
    {
        $this->setExpectedException( $expectedException );

        $this->assertEquals( $expectedDumpMetaInformation, DumpMetaInformation::get( $this->db, $dataSourceIds ) );
    }

    /**
     * @return array
     */
    public function getDumpMetaInformationDataProvider()
    {
        return array(
            // Single id
            array (
                new ItemId( 'Q1' ),
                $this->dumpMetaInformation[ 1 ],
                null
            ),
            // Multiple ids
            array(
                array(
                    new ItemId( 'Q1' ),
                    new ItemId( 'Q2' )
                ),
                array(
                    1 => $this->dumpMetaInformation[ 1 ],
                    2 => $this->dumpMetaInformation[ 2 ]
                ),
                null
            ),
            // No specific ids
            array(
                null,
                array(
                    1 => $this->dumpMetaInformation[ 1 ],
                    2 => $this->dumpMetaInformation[ 2 ]
                ),
                null
            ),
            // Non-existent id
            array(
                new ItemId( 'Q3' ),
                null,
                null
            ),
            // Invalid ids
            array(
                'broken',
                null,
                'InvalidArgumentException'
            ),
            array(
                array( 'broken' ),
                null,
                'InvalidArgumentException'
            )
        );
    }


    /**
     * @dataProvider saveDumpMetaInformationDataProvider
     */
    public function testSaveDumpMetaInformation( $sourceItemId, $importDate, $language, $sourceUrl, $size, $license ){

        $dumpMetaInformation = new DumpMetaInformation( $sourceItemId, $importDate, $language, $sourceUrl, $size, $license );
        $dumpMetaInformation->save( $this->db );

        $metaInformationFromDatabase = DumpMetaInformation::get( $this->db, $sourceItemId );

        $this->assertEquals( $dumpMetaInformation, $metaInformationFromDatabase );
    }


    /**
     * Test cases for testSaveDumpMetaInformation
     */
    public function saveDumpMetaInformationDataProvider()
    {

        return array(
            // Update existing one
            array(
                new ItemId( 'Q1' ),
                new DateTime( '2015-01-01 00:00:00' ),
                'de',
                'http://www.foo.bar',
                42,
                'CC0'
            ),
            // Insert new one
            array(
                new ItemId( 'Q3' ),
                new DateTime( '2015-01-01 00:00:00' ),
                'en',
                'http://www.foo.bar',
                42,
                'CC0'
            )
        );
    }
}
 