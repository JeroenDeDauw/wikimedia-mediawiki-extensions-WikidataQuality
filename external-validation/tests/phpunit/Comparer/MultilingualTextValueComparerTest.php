<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\MonolingualTextValue;
use DataValues\MultilingualTextValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer
 *
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer

 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class MultilingualTextValueComparerTest extends DataValueComparerTestBase
{
    /**
     * @dataProvider constructDataProvider
     */
    public function testConstruct( $dumpMetaInformation, $dataValue, $externalValues, $expectedDataValue )
    {
        $comparer = $this->createComparer( $dumpMetaInformation, $dataValue, $externalValues );

        $this->assertEquals( $dumpMetaInformation, $comparer->getDumpMetaInformation() );
        $this->assertEquals( $expectedDataValue, $comparer->getDataValue() );
        $this->assertEquals( $externalValues, $comparer->getExternalValues() );
    }

    public function constructDataProvider()
    {
        $monolingualTextValue = new MonolingualTextValue( 'en', 'foo' );

        return array(
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( $monolingualTextValue ) ),
                array( 'foo', 'bar' ),
                $monolingualTextValue
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( $monolingualTextValue ) ),
                array( 'foo', 'bar' ),
                null
            )
        );
    }


    /**
     * Test cases for testExecute
     * @return array
     */
    public function executeDataProvider()
    {
        return array(
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( new MonolingualTextValue( 'en', 'foo' ) ) ),
                array( 'foo', 'bar' ),
                true,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( new MonolingualTextValue( 'en', 'foo' ) ) ),
                array( 'foobar', 'bar' ),
                false,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( new MonolingualTextValue( 'en', 'foo' ) ) ),
                array( 'foo', 'bar' ),
                false,
                array()
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( new MonolingualTextValue( 'de', 'foo' ) ) ),
                array( 'foo', 'bar' ),
                false,
                array()
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( new MonolingualTextValue( 'en', 'foo' ) ) ),
                null,
                false,
                array( 'foo' )
            )
        );
    }

    protected function createComparer( $dumpMetaInformation, $dataValue, $externalValues )
    {
        return new MultilingualTextValueComparer( $dumpMetaInformation, $dataValue, $externalValues );
    }
}