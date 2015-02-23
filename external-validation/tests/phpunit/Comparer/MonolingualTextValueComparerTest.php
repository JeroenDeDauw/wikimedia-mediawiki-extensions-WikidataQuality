<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\MonolingualTextValue;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer
 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class MonolingualTextValueComparerTest extends \PHPUnit_Framework_TestCase {
    private $testDumpMetaInformation;
    private $testDataValue;


    protected function setUp() {
        parent::setUp();
        $this->testDumpMetaInformation = new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' );
        $this->testDataValue = new MonolingualTextValue( 'en', 'foo' );
    }

    protected function tearDown() {
        unset( $this->testDumpMetaInformation );
        unset( $this->testDataValue );
        parent::tearDown();
    }


    public function testExecuteOne() {
        $comparer = new MonolingualTextValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( 'foo', 'bar' ) );
        $this->assertTrue( $comparer->execute() );

        $this->assertEquals( $comparer->getLocalValues(), array( $this->testDataValue->getText() ) );
    }

    public function testExecuteTwo() {
        $comparer = new MonolingualTextValueComparer( $this->testDumpMetaInformation, $this->testDataValue, array( 'bar', 'foobar' ) );
        $this->assertFalse( $comparer->execute() );

        $this->assertEquals( $comparer->getLocalValues(), array( $this->testDataValue->getText() ) );
    }

    public function testExecuteThree() {
        $comparer = new MonolingualTextValueComparer( $this->testDumpMetaInformation, $this->testDataValue, null );
        $this->assertFalse( $comparer->execute() );

        $this->assertEquals( $comparer->getLocalValues(), array( $this->testDataValue->getText() ) );
    }
}