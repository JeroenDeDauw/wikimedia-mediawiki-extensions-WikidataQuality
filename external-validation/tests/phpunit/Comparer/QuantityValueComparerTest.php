<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\QuantityValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer
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
class QuantityValueComparerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers       WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer::execute
     * @dataProvider executeDataProvider
     */
    public function testExecute( $dumpMetaInformation, $dataValue, $externalValues, $expectedResult, $expectedLocalValues )
    {
        $comparer = new QuantityValueComparer( $dumpMetaInformation, $dataValue, $externalValues );

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
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                QuantityValue::newFromNumber( 42, '1', 44, 40 ),
                array( '42' ),
                true,
                array( '42±2' )
            ),
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                QuantityValue::newFromNumber( 42, '1', 44, 40 ),
                array( '41' ),
                true,
                array( '42±2' )
            ),
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                QuantityValue::newFromNumber( 42, '1', 44, 40 ),
                array( '23' ),
                false,
                array( '42±2' )
            ),
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                QuantityValue::newFromNumber( 42, '1' ),
                array( '42' ),
                true,
                array( '42' )
            ),
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                QuantityValue::newFromNumber( 42, '1' ),
                array( '44' ),
                false,
                array( '42' )
            ),
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                QuantityValue::newFromNumber( 42, '1', 44, 40 ),
                null,
                false,
                array( '42±2' )
            )
        );
    }
}