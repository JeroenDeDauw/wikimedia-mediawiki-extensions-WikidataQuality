<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\StringValue;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer
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
class StringValueComparerTest extends \PHPUnit_Framework_TestCase {
    private $testDumpMetaInformation;
    private $testDataValue;


    protected function setUp() {
        parent::setUp();
        $this->testDumpMetaInformation = new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' );
        $this->testDataValue = new StringValue( 'foo' );
    }

    protected function tearDown() {
        unset( $this->testDumpMetaInformation, $this->testDataValue );
        parent::tearDown();
    }


    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer::execute
     */
    public function testExecuteOne() {
        $comparer = new StringValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( 'foo', 'bar' ) );
        $this->assertTrue( $comparer->execute() );

        $this->assertEquals( array( $this->testDataValue->getValue() ), $comparer->getLocalValues() );
    }

    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer::execute
     */
    public function testExecuteTwo() {
        $comparer = new StringValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( 'bar', 'foobar' ) );
        $this->assertFalse( $comparer->execute() );

        $this->assertEquals( array( $this->testDataValue->getValue() ), $comparer->getLocalValues() );
    }

    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer::execute
     */
    public function testExecuteThree() {
        $comparer = new StringValueComparer( $this->testDumpMetaInformation, $this->testDataValue, null );
        $this->assertFalse( $comparer->execute() );

        $this->assertEquals( array( $this->testDataValue->getValue() ), $comparer->getLocalValues() );
    }
}