<?php

namespace WikidataQuality\ExternalValidation\Tests\DumpMetaInformation;

use WikidataQuality\ExternalValidation\DumpMetaInformation;
use Wikibase\DataModel\Entity\ItemId;
use DateTime;

/**
 * @covers WikidataQuality\ExternalValidation\DumpMetaInformation
 *
 * @group Database
 * @group medium
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
            1,
            new ItemId( 'Q36578' ),
            new DateTime( '2015-01-01 00:00:00' ),
            'en',
            'http://www.foo.bar',
            42,
            'CC0'
        );
        $this->dumpMetaInformation[ 2 ] = new DumpMetaInformation(
            2,
            new ItemId( 'Q23213' ),
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
                    'dump_id' => $dumpMetaInformation->getDumpId(),
                    'source_item_id' => $dumpMetaInformation->getSourceItemId()->getNumericId(),
                    'import_date' => $dumpMetaInformation->getImportDate()->format( DateTime::ISO8601 ),
                    'language' => $dumpMetaInformation->getLanguage(),
                    'source_url' => $dumpMetaInformation->getSourceUrl(),
                    'size' => $dumpMetaInformation->getSize(),
                    'license' =>  $dumpMetaInformation->getLicense()
                )
            );
        }
    }


    /**
     * @dataProvider constructValidArgumentsDataProvider
     */
    public function testConstructValidArguments( $dumpId, $sourceItemId, $importDate, $language, $sourceUrl, $size, $license )
    {
        $metaInformation = new DumpMetaInformation( $dumpId, $sourceItemId, $importDate, $language, $sourceUrl, $size, $license );

        $this->assertEquals( $dumpId, $metaInformation->getDumpId() );
        $this->assertEquals( new ItemId( 'Q123' ), $metaInformation->getSourceItemId() );
        $this->assertEquals( $importDate, $metaInformation->getImportDate() );
        $this->assertEquals( $language, $metaInformation->getLanguage() );
        $this->assertEquals( $sourceUrl, $metaInformation->getSourceUrl() );
        $this->assertEquals( $size, $metaInformation->getSize() );
        $this->assertEquals( $license, $metaInformation->getLicense() );
    }

    /**
     * Test cases for testConstructValidArguments
     * @return array
     */
    public function constructValidArgumentsDataProvider()
    {
        $dumpId = 123;
        $itemId = new ItemId( 'Q123' );
        $importDate = new DateTime( '30-11-2015' );
        $language = 'de';
        $sourceUrl = 'http://randomurl.tld';
        $size = 42;
        $license = 'CC0';

        return array(
            array(
                $dumpId,
                $itemId,
                $importDate,
                $language,
                $sourceUrl,
                $size,
                $license
            ),
            array(
                $dumpId,
                $itemId->getSerialization(),
                $importDate,
                $language,
                $sourceUrl,
                $size,
                $license
            ),
            array(
                $dumpId,
                (string)$itemId->getNumericId(),
                $importDate,
                $language,
                $sourceUrl,
                $size,
                $license
            )
        );
    }


    /**
     * @dataProvider constructInvalidArgumentsDataProvider
     */
    public function testConstructInvalidArguments( $dumpId, $sourceItemId, $importDate, $language, $sourceUrl, $size, $license )
    {
        $this->setExpectedException( 'InvalidArgumentException' );

        new DumpMetaInformation( $dumpId, $sourceItemId, $importDate, $language, $sourceUrl, $size, $license );
    }

    /**
     * Test cases for testConstructInvalidArguments
     * @return array
     */
    public function constructInvalidArgumentsDataProvider()
    {
        $dumpId = '123';
        $importDate = new DateTime( '2015-03-17' );
        $language = 'de';
        $sourceUrl = 'http://randomurl.tld';
        $size = 42;
        $license = 'CC0';

        return array(
            array(
                $dumpId,
                123,
                $importDate,
                $language,
                $sourceUrl,
                $size,
                $license
            ),
            array(
                $dumpId,
                new ItemId( 'Q123' ),
                '2015-03-17',
                $language,
                $sourceUrl,
                $size,
                $license
            )
        );
    }


    /**
     * @dataProvider getDumpMetaInformationDataProvider
     */
    public function testGetDumpMetaInformation( $dumpIds, $expectedDumpMetaInformation, $expectedException )
    {
        $this->setExpectedException( $expectedException );

        $this->assertEquals( $expectedDumpMetaInformation, DumpMetaInformation::get( $this->db, $dumpIds ) );
    }

    /**
     * @return array
     */
    public function getDumpMetaInformationDataProvider()
    {
        return array(
            // Single id
            array (
                1,
                $this->dumpMetaInformation[ 1 ],
                null
            ),
            // Multiple ids
            array(
                array( 1, 2 ),
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
                3,
                null,
                null
            ),
            // Invalid id
            array(
                'broken',
                null,
                'InvalidArgumentException'
            )
        );
    }


    /**
     * @dataProvider saveDumpMetaInformationDataProvider
     */
    public function testSaveDumpMetaInformation( $dumpId, $sourceItemId, $importDate, $language, $sourceUrl, $size, $license ){

        $dumpMetaInformation = new DumpMetaInformation( $dumpId, $sourceItemId, $importDate, $language, $sourceUrl, $size, $license );
        $dumpMetaInformation->save( $this->db );

        $metaInformationFromDatabase = DumpMetaInformation::get( $this->db, $dumpId );

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
                1,
                '36578',
                new DateTime( '2015-01-01 00:00:00' ),
                'de',
                'http://www.foo.bar',
                42,
                'CC0'
            ),
            // Insert new one
            array(
                3,
                '36578',
                new DateTime( '2015-01-01 00:00:00' ),
                'en',
                'http://www.foo.bar',
                42,
                'CC0'
            )
        );
    }
}
 