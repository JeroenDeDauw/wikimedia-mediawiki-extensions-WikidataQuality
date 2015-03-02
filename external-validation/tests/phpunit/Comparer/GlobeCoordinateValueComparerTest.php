<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer
 *
 * @uses WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation
 * @uses WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class GlobeCoordinateValueComparerTest extends \PHPUnit_Framework_TestCase {
    private $testDumpMetaInformation;
    private $testDataValue;
    private $shownValue;


    protected function setUp() {
        parent::setUp();
        $this->testDumpMetaInformation = new DumpMetaInformation( 'xml', 'de', 'd.m.Y', 'TestDB' );
        $this->testDataValue = new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1, null );
        $this->shownValue = '64° N, 26° E';
    }

    protected function tearDown() {
        unset( $this->testDumpMetaInformation, $this->testDataValue, $this->shownValue );
        parent::tearDown();
    }


    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer::execute
     */
    public function testExecuteOne() {
        $comparer = new GlobeCoordinateValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( '64.000000 N, 26.000000 E' ) );
        $this->assertTrue( $comparer->execute() );

        $this->assertEquals( array( $this->shownValue ), $comparer->getLocalValues() );
        $this->assertEquals( array( $this->shownValue ), $comparer->getExternalValues() );
    }

    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer::execute
     */
    public function testExecuteTwo() {
        $comparer = new GlobeCoordinateValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( '64 N, 26 E' ) );
        $this->assertTrue( $comparer->execute() );

        $this->assertEquals( array( $this->shownValue ), $comparer->getLocalValues() );
        $this->assertEquals( array( $this->shownValue ), $comparer->getExternalValues() );
    }

    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer::execute
     */
    public function testExecuteThree() {
        $comparer = new GlobeCoordinateValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( '64.000001 N, 26.000010 E' ) );
        $this->assertFalse( $comparer->execute() );

        $this->assertEquals( array( $this->shownValue ), $comparer->getLocalValues() );
        $this->assertNotEquals( array( $this->shownValue ), $comparer->getExternalValues() );
    }
}