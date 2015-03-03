<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\TimeValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer
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
class TimeValueComparerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers       WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer::execute
     * @dataProvider executeDataProvider
     */
    public function testExecute( $dumpMetaInformation, $dataValue, $externalValues, $expectedResult, $expectedLocalValues )
    {
        $comparer = new TimeValueComparer( $dumpMetaInformation, $dataValue, $externalValues );

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
                new TimeValue( '+0000000000001955-03-11T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' ),
                array( '11.03.1955' ),
                true,
                array( '11 March 1955' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new TimeValue( '+0000000000001955-03-11T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' ),
                array( '1955-03-11' ),
                true,
                array( '11 March 1955' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new TimeValue( '+0000000000001955-03-11T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' ),
                array( '1991-05-23' ),
                false,
                array( '11 March 1955' )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new TimeValue( '+0000000000002015-03-11T00:00:00Z', 0, 0, 0, 9, 'http://www.wikidata.org/entity/Q1985727' ),
                array( '2015' ),
                true,
                array( '2015' ),
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new TimeValue( '+0000000000001955-03-11T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' ),
                null,
                false,
                array( '11 March 1955' )
            )
        );
    }
}