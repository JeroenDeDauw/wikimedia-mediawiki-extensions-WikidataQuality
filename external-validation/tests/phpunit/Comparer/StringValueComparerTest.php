<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\StringValue;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer
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
        unset( $this->testDumpMetaInformation );
        unset( $this->testDataValue );
        parent::tearDown();
    }


    public function testExecuteOne() {
        $comparer = new StringValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( 'foo', 'bar' ) );
        $this->assertTrue( $comparer->execute() );

        $this->assertEquals( $comparer->getLocalValues(), array( $this->testDataValue->getValue() ) );
    }

    public function testExecuteTwo() {
        $comparer = new StringValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( 'bar', 'foobar' ) );
        $this->assertFalse( $comparer->execute() );

        $this->assertEquals( $comparer->getLocalValues(), array( $this->testDataValue->getValue() ) );
    }

    public function testExecuteThree() {
        $comparer = new StringValueComparer( $this->testDumpMetaInformation, $this->testDataValue, null );
        $this->assertFalse( $comparer->execute() );

        $this->assertEquals( $comparer->getLocalValues(), array( $this->testDataValue->getValue() ) );
    }
}