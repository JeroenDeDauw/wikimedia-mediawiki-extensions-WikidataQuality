<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\MonolingualTextValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer
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
class MonolingualTextValueComparerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers       WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer::execute
     * @dataProvider executeDataProvider
     */
    public function testExecute( $dumpMetaInformation, $dataValue, $externalValues, $expectedResult, $expectedLocalValues )
    {
        $comparer = new MonolingualTextValueComparer( $dumpMetaInformation, $dataValue, $externalValues );

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
                new MonolingualTextValue( 'en', 'foo' ),
                array( 'foo', 'bar' ),
                true,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MonolingualTextValue( 'en', 'foo' ),
                array( 'foobar', 'bar' ),
                false,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new MonolingualTextValue( 'en', 'foo' ),
                array( 'foo', 'bar' ),
                true,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MonolingualTextValue( 'en', 'foo' ),
                null,
                false,
                array( 'foo' )
            )
        );
    }
}