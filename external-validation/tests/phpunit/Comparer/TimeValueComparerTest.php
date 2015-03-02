<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\TimeValue;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer
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
class TimeValueComparerTest extends \PHPUnit_Framework_TestCase {
    private $testDumpMetaInformation;
    private $testDataValue;
    private $shownValue;


    protected function setUp() {
        parent::setUp();
        $this->testDumpMetaInformation = new DumpMetaInformation( 'xml', 'de', 'd.m.Y', 'TestDB' );
        $this->testDataValue = new TimeValue( '+00000001955-03-11T00:00:00Z', 0, 0, 0, 11, 'gregorian' );
        $this->shownValue = '11 März 1955';
    }

    protected function tearDown() {
        unset( $this->testDumpMetaInformation, $this->testDataValue, $this->shownValue );
        parent::tearDown();
    }


    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer::execute
     */
    public function testExecuteOne() {
        $comparer = new TimeValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( '11.03.1955' ) );
        $this->assertTrue( $comparer->execute() );

        $this->assertEquals( array( $this->shownValue ), $comparer->getLocalValues() );
        $this->assertEquals( array( $this->shownValue ), $comparer->getExternalValues() );
    }

    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer::execute
     */
    public function testExecuteTwo() {
        $comparer = new TimeValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( '1955-03-11' ) );
        $this->assertTrue( $comparer->execute() );

        $this->assertEquals( array( $this->shownValue ), $comparer->getLocalValues() );
        $this->assertEquals( array( $this->shownValue ), $comparer->getExternalValues() );
    }

    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer::execute
     */
    public function testExecuteThree() {
        $comparer = new TimeValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( '11 Mar 1955' ) );
        $this->assertTrue( $comparer->execute() );

        $this->assertEquals( array( $this->shownValue ), $comparer->getLocalValues() );
        $this->assertEquals( array( $this->shownValue ), $comparer->getExternalValues() );
    }
}