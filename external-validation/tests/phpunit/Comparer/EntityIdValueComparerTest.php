<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\MonolingualTextValue;
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
                array (
                    new MonolingualTextValue( 'en', 'foo' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new EntityIdValue( new ItemId( 'Q1' ) ),
                array( 'baz' ),
                false,
                array(
                    new MonolingualTextValue( 'en', 'baz' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new EntityIdValue( new ItemId( 'Q1' ) ),
                array( 'Fubar' ),
                true,
                array(
                    new MonolingualTextValue( 'de', 'Fubar' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'es', 'Y-m-d', 'TestDB' ),
                new EntityIdValue( new ItemId( 'Q1' ) ),
                array( 'foo' ),
                false,
                array(
                    new MonolingualTextValue( 'es', 'foo' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new EntityIdValue( new ItemId( 'Q1' ) ),
                null,
                false,
                null
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new EntityIdValue( new ItemId( 'Q2' ) ),
                array( 'foo' ),
                false,
                array(
                    new MonolingualTextValue( 'en', 'foo' )
                )
            )
        );
    }


    protected function createComparer( $dumpMetaInformation, $localValue, $externalValues )
    {
        $mock = $this->getMockBuilder( 'WikidataQuality\ExternalValidation\CrossCheck\Comparer\EntityIdValueComparer' )
            ->setMethods( array( 'getEntityLookup' ) )
            ->setConstructorArgs( array( $dumpMetaInformation, $localValue, $externalValues ) )
            ->getMock();
        $mock->method( 'getEntityLookup' )
            ->willReturn( new JsonFileEntityLookup( __DIR__ . '/testdata' ) );

        return $mock;
    }
}