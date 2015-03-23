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
class DumpMetaInformationTest extends \MediaWikiTestCase {

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
        $this->db->insert(
            DUMP_META_TABLE,
            array(
                array(
                    'row_id' => 1,
                    'source_item_id' => 36578,
                    'import_date' => '2015-01-01 00:00:00',
                    'language' => 'en',
                    'source_url' => 'http://www.foo.bar',
                    'size' => 42,
                    'license' =>  'CC0'
                )
            )
        );
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
        $importDate = new DateTime( '30-11-2015' );
        $language = 'de';
        $sourceUrl = 'http://randomurl.tld';
        $size = 42;
        $license = 'CC0';

        return array(
            array(
                $dumpId,
                new ItemId( 'Q123' ),
                $importDate,
                $language,
                $sourceUrl,
                $size,
                $license
            ),
            array(
                $dumpId,
                '123',
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
        $dumpId = 123;
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

    public function testGetDumpMetaInformation(){

        $dumpMetaInformation = new DumpMetaInformation(
            1,
            '36578',
            new DateTime( '2015-01-01 00:00:00' ),
            'en',
            'http://www.foo.bar',
            42,
            'CC0' );

        $metaInformationFromDatabase = DumpMetaInformation::get( $this->db, 1 );

        $this->assertEquals( $metaInformationFromDatabase->getDumpId(), $dumpMetaInformation->getDumpId() );
        $this->assertEquals( $metaInformationFromDatabase->getSourceItemId(), $dumpMetaInformation->getSourceItemId() );
        $this->assertEquals( $metaInformationFromDatabase->getImportDate()->format(DateTime::ISO8601), $dumpMetaInformation->getImportDate()->format(DateTime::ISO8601) );
        $this->assertEquals( $metaInformationFromDatabase->getLanguage(), $dumpMetaInformation->getLanguage() );
        $this->assertEquals( $metaInformationFromDatabase->getSourceUrl(), $dumpMetaInformation->getSourceUrl() );
        $this->assertEquals( $metaInformationFromDatabase->getSize(), $dumpMetaInformation->getSize() );
        $this->assertEquals( $metaInformationFromDatabase->getLicense(), $dumpMetaInformation->getLicense() );
    }

    /**
     * @dataProvider saveDumpMetaInformationDataProvider
     */
    public function testSaveDumpMetaInformation( $dumpId, $sourceItemId, $importDate, $language, $sourceUrl, $size, $license ){

        $dumpMetaInformation = new DumpMetaInformation( $dumpId, $sourceItemId, $importDate, $language, $sourceUrl, $size, $license );
        $dumpMetaInformation->save( $this->db );

        $metaInformationFromDatabase = DumpMetaInformation::get( $this->db, $dumpId );

        $this->assertEquals( $metaInformationFromDatabase->getDumpId(), $dumpMetaInformation->getDumpId() );
        $this->assertEquals( $metaInformationFromDatabase->getSourceItemId(), $dumpMetaInformation->getSourceItemId() );
        $this->assertEquals( $metaInformationFromDatabase->getImportDate()->format(DateTime::ISO8601), $dumpMetaInformation->getImportDate()->format(DateTime::ISO8601) );
        $this->assertEquals( $metaInformationFromDatabase->getLanguage(), $dumpMetaInformation->getLanguage() );
        $this->assertEquals( $metaInformationFromDatabase->getSourceUrl(), $dumpMetaInformation->getSourceUrl() );
        $this->assertEquals( $metaInformationFromDatabase->getSize(), $dumpMetaInformation->getSize() );
        $this->assertEquals( $metaInformationFromDatabase->getLicense(), $dumpMetaInformation->getLicense() );
    }


    /**
     * Test cases for testSaveDumpMetaInformation
     */
    public function saveDumpMetaInformationDataProvider()
    {

        return array(
            array(
                1,
                '36578',
                new DateTime( '2015-01-01 00:00:00' ),
                'de',
                'http://www.foo.bar',
                42,
                'CC0'
            ),
            array(
                2,
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
 