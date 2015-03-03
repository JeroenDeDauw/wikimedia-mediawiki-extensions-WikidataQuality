<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\StringValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer
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
class StringValueComparerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers       WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer::execute
     * @dataProvider executeDataProvider
     */
    public function testExecute( $dumpMetaInformation, $dataValue, $externalValues, $expectedResult, $expectedLocalValues )
    {
        $comparer = new StringValueComparer( $dumpMetaInformation, $dataValue, $externalValues );

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
                new StringValue( 'foo' ),
                array( 'foo', 'bar' ),
                true,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new StringValue( 'foo' ),
                array( 'foobar', 'bar' ),
                false,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new StringValue( 'foobar' ),
                array( 'foobar', 'bar' ),
                true,
                array( 'foobar' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new StringValue( 'foo' ),
                null,
                false,
                array( 'foo' )
            )
        );
    }
}