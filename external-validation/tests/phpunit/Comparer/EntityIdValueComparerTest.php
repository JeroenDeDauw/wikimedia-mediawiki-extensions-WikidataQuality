<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\EntityIdValueComparer
 *
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class EntityIdValueComparerTest extends DataValueComparerTestBase
{
    /**
     * Test cases for testExecute
     * @return array
     */
    public function executeDataProvider()
    {
        return array(
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new EntityIdValue( new ItemId( 'Q1' ) ),
                array( 'foo' ),
                true,
                array( 'foobar', 'foo', 'bar' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new EntityIdValue( new ItemId( 'Q1' ) ),
                array( 'baz' ),
                false,
                array( 'foobar', 'foo', 'bar' )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new EntityIdValue( new ItemId( 'Q1' ) ),
                array( 'Fubar' ),
                true,
                array( 'foobar', 'Fubar' )
            ),
            array(
                new DumpMetaInformation( 'json', 'es', 'Y-m-d', 'TestDB' ),
                new EntityIdValue( new ItemId( 'Q1' ) ),
                array( 'foo' ),
                false,
                array()
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new EntityIdValue( new ItemId( 'Q1' ) ),
                null,
                false,
                array( 'foobar', 'foo', 'bar' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new EntityIdValue( new ItemId( 'Q2' ) ),
                array( 'foo' ),
                false,
                null
            )
        );
    }


    protected function createComparer( $dumpMetaInformation, $dataValue, $externalValues )
    {
        $mock = $this->getMockBuilder( 'WikidataQuality\ExternalValidation\CrossCheck\Comparer\EntityIdValueComparer' )
            ->setMethods( array( 'getEntityLookup' ) )
            ->setConstructorArgs( array( $dumpMetaInformation, $dataValue, $externalValues ) )
            ->getMock();
        $mock->method( 'getEntityLookup' )
            ->willReturn( new JsonFileEntityLookup( __DIR__ . '/testdata' ) );

        return $mock;
    }
}