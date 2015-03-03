<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer
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
class GlobeCoordinateValueComparerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers       WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer::execute
     * @dataProvider executeDataProvider
     */
    public function testExecute( $dumpMetaInformation, $dataValue, $externalValues, $expectedResult, $expectedLocalValues )
    {
        $comparer = new GlobeCoordinateValueComparer( $dumpMetaInformation, $dataValue, $externalValues );

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
                new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1, null ),
                array( '64.000000 N, 26.000000 E' ),
                true,
                array( '64, 26' )
            ),
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1, null ),
                array( '64 N, 26 E' ),
                true,
                array( '64, 26' )
            ),
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1, null ),
                array( '64.000001 N, 26.000010 E' ),
                false,
                array( '64, 26' )
            ),
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1, null ),
                null,
                false,
                array( '64, 26' )
            )
        );
    }
}