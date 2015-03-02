<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\TimeValue;
use DataValues\StringValue;
use DataValues\DecimalValue;
use DataValues\QuantityValue;
use DataValues\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
use DataValues\MonolingualTextValue;
use DataValues\MultilingualTextValue;
use DataValues\UnknownValue;
use JsonSchema\Constraints\String;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\EntityIdValue;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer;

/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 *
 * @uses WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation
 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class DataValueComparerTest extends \PHPUnit_Framework_TestCase {
    private $testDumpMetaInformation;
    private $testDataValue;
    private $testExternalValues;


    protected function setUp() {
        parent::setUp();
        $this->testDumpMetaInformation = new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' );
        $this->testDataValue = new StringValue( 'foo' );
        $this->testExternalValues = array( 'foo', 'bar' );
    }

    protected function tearDown() {
        unset( $this->testDumpMetaInformation, $this->testDataValue, $this->testExternalValues );
        parent::tearDown();
    }


    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer::__construct
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer::getDumpMetaInformation
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer::getDataValue
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer::getLocalValues
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer::getExternalValues
     */
    public function testConstructOne() {
        $comparerMock = $this->getDataValueComparerMock( $this->testDumpMetaInformation, $this->testDataValue, $this->testExternalValues );
        $this->assertEquals( $this->testDumpMetaInformation, $comparerMock->getDumpMetaInformation() );
        $this->assertEquals( $this->testDataValue, $comparerMock->getDataValue() );
        $this->assertEquals( $this->testExternalValues, $comparerMock->getExternalValues() );
        $this->assertNull( $comparerMock->getLocalValues() );
    }

    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer::__construct
     */
    public function testConstructTwo() {
        $this->setExpectedException( 'InvalidArgumentException' );
        $this->getDataValueComparerMock( $this->testDumpMetaInformation, $this->testDataValue, 'foo' );
    }


    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer::getComparer
     * @dataProvider getComparerProvider
     */
    public function testGetComparer( $dataValue, $comparerClass ) {
        $comparer = DataValueComparer::getComparer( $this->testDumpMetaInformation, $dataValue, $this->testExternalValues );
        if( $comparerClass ) {
            $this->assertInstanceOf( $comparerClass, $comparer );
        }
        else {
            $this->assertNull( $comparer );
        }
    }

    public function getComparerProvider() {
        return array(
            array( new EntityIdValue( new ItemId( 'Q42' ) ), 'WikidataQuality\ExternalValidation\CrossCheck\Comparer\EntityIdValueComparer' ),
            array( new MonolingualTextValue( 'en', 'foo' ), 'WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer' ),
            array( new MultilingualTextValue( array( new MonolingualTextValue( 'en', 'foo' ) ) ), 'WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer' ),
            array( new QuantityValue( new  DecimalValue( 42 ), '1', new DecimalValue( 42 ), new DecimalValue(42 ) ), 'WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer' ),
            array( new StringValue( 'foo' ), 'WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer' ),
            array( new TimeValue( '+00000002013-12-07T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' ), 'WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer' ),
            array( new GlobeCoordinateValue( new LatLongValue( 52.5, 13.3 ), 0.016 ), 'WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer' ),
            array( new UnknownValue( null ), null )
        );
    }


    /**
     * Returns DataValueComparer mock with given arguments
     * @param DumpMetaInformation $dumpMetaInformation
     * @param DataValue $dataValue
     * @param array $externalValues
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDataValueComparerMock( $dumpMetaInformation, $dataValue, $externalValues ) {
        return $this->getMockForAbstractClass(
            'WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer',
            array( $dumpMetaInformation, $dataValue, $externalValues )
        );
    }
}