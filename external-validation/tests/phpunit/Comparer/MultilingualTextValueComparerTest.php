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
    public function testConstruct( $dumpMetaInformation, $localValue, $externalValues )
    {
        $comparer = $this->createComparer( $dumpMetaInformation, $localValue, $externalValues );

        $this->assertEquals( $dumpMetaInformation, $comparer->getDumpMetaInformation() );
        $this->assertEquals( $localValue, $comparer->getLocalValue() );
        $this->assertEquals( $externalValues, $comparer->getExternalValues() );
    }

    public function constructDataProvider()
    {
        $monolingualTextValue = new MonolingualTextValue( 'en', 'foo' );

        return array(
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( $monolingualTextValue ) ),
                array( 'foo', 'bar' )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( $monolingualTextValue ) ),
                array( 'foo', 'bar' )
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
                array(
                    new MonolingualTextValue( 'en', 'foo' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( new MonolingualTextValue( 'en', 'foo' ) ) ),
                array( 'foobar', 'bar' ),
                false,
                array(
                    new MonolingualTextValue( 'en', 'foobar' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( new MonolingualTextValue( 'en', 'foo' ) ) ),
                array( 'foo', 'bar' ),
                false,
                array(
                    new MonolingualTextValue( 'de', 'foo' ),
                    new MonolingualTextValue( 'de', 'bar' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( new MonolingualTextValue( 'de', 'foo' ) ) ),
                array( 'foo', 'bar' ),
                false,
                array(
                    new MonolingualTextValue( 'en', 'foo' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MultilingualTextValue( array( new MonolingualTextValue( 'en', 'foo' ) ) ),
                null,
                false,
                null
            )
        );
    }

    protected function createComparer( $dumpMetaInformation, $localValue, $externalValues )
    {
        return new MultilingualTextValueComparer( $dumpMetaInformation, $localValue, $externalValues );
    }
}