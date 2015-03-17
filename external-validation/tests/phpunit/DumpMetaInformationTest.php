<?php

namespace WikidataQuality\ExternalValidation\Tests\DumpMetaInformation;

use WikidataQuality\ExternalValidation\DumpMetaInformation;
use Wikibase\DataModel\Entity\ItemId;
use DateTime;

/**
 * @covers WikidataQuality\ExternalValidation\DumpMetaInformation
 *
 * @author BP2014N1
 * @license GNU GPL v2+exte
 */
class DumpMetaInformationTest extends \MediaWikiTestCase {

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
}
 