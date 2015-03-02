<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\DecimalValue;
use DataValues\QuantityValue;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer
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
class QuantityValueComparerTest extends \PHPUnit_Framework_TestCase {
    private $testDumpMetaInformation;
    private $testAmount;
    private $testUpperBound;
    private $testLowerBound;
    private $testDataValue;


    protected function setUp() {
        parent::setUp();
        $this->testDumpMetaInformation = new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' );
        $this->testAmount = new DecimalValue( 42 );
        $this->testUpperBound = new DecimalValue( 44 );
        $this->testLowerBound = new DecimalValue( 40 );
        $this->testDataValue = new QuantityValue( $this->testAmount, '1', $this->testUpperBound, $this->testLowerBound );
    }

    protected function tearDown() {
        unset( $this->testDumpMetaInformation, $this->testDataValue );
        parent::tearDown();
    }

    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer::execute
     */
    public function testExecuteOne() {
        $comparer = new QuantityValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( '42' ) );
        $this->assertTrue( $comparer->execute() );
    }

    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer::execute
     */
    public function testExecuteTwo() {
        $comparer = new QuantityValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( '41' ) );
        $this->assertTrue( $comparer->execute() );
    }

    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer::execute
     */
    public function testExecuteThree() {
        $comparer = new QuantityValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( '44' ) );
        $this->assertTrue( $comparer->execute() );
    }

    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer::execute
     */
    public function testExecuteFour() {
        $comparer = new QuantityValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( '23' ) );
        $this->assertFalse( $comparer->execute() );
    }
}