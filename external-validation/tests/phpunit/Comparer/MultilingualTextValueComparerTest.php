<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\MonolingualTextValue;
use DataValues\MultilingualTextValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer
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
class MultilingualTextValueComparerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers       WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer::__construct
     * @dataProvider constructDataProvider
     */
    public function testConstruct( $dumpMetaInformation, $dataValue, $externalValues, $expectedDataValue )
    {
        $comparer = new MultilingualTextValueComparer( $dumpMetaInformation, $dataValue, $externalValues );

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
     * @covers       WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer::execute
     * @uses         WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer
     * @dataProvider executeDataProvider
     */
    public function testExecute( $dumpMetaInformation, $dataValue, $externalValues, $expectedResult, $expectedLocalValues )
    {
        $comparer = new MultilingualTextValueComparer( $dumpMetaInformation, $dataValue, $externalValues );

        $this->assertEquals( $expectedResult, $comparer->execute() );
        if ( is_array( $expectedLocalValues ) ) {
            $this->assertSame(
                array_diff( $expectedLocalValues, $comparer->getLocalValues() ),
                array_diff( $comparer->getLocalValues(), $expectedLocalValues )
            );
        } else {
            $this->assertEquals( $expectedLocalValues, $comparer->getLocalValues() );
        }
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
}