<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\DecimalValue;
use DataValues\Geo\Values\LatLongValue;
use DataValues\GlobeCoordinateValue;
use DataValues\MonolingualTextValue;
use DataValues\MultilingualTextValue;
use DataValues\QuantityValue;
use DataValues\StringValue;
use DataValues\TimeValue;
use DataValues\UnknownValue;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;

/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 *
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation
 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class DataValueComparerTest extends \PHPUnit_Framework_TestCase
{
    private $testDumpMetaInformation;
    private $testDataValue;
    private $testExternalValues;


    protected function setUp()
    {
        parent::setUp();
        $this->testDumpMetaInformation = new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' );
        $this->testDataValue = new StringValue( 'foo' );
        $this->testExternalValues = array( 'foo', 'bar' );
    }

    protected function tearDown()
    {
        unset( $this->testDumpMetaInformation, $this->testDataValue, $this->testExternalValues );
        parent::tearDown();
    }


    /**
     * @dataProvider constructValidArgumentsDataProvider
     */
    public function testConstructValidArguments( $dumpMetaInformation, $dataValue, $externalValues )
    {
        $comparerMock = $this->getDataValueComparerMock( $dumpMetaInformation, $dataValue, $externalValues );

        $this->assertEquals( $dumpMetaInformation, $comparerMock->getDumpMetaInformation() );
        $this->assertEquals( $dataValue, $comparerMock->getDataValue() );
        $this->assertEquals( $externalValues, $comparerMock->getExternalValues() );
        $this->assertNull( $comparerMock->getLocalValues() );
    }

    /**
     * Test cases for testConstructValidArguments
     * @return array
     */
    public function constructValidArgumentsDataProvider()
    {
        return array(
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new StringValue( 'foo' ),
                array( 'foo', 'bar' )
            )
        );
    }


    public function testConstructInvalidArguments()
    {
        $this->setExpectedException( 'InvalidArgumentException' );

        $this->getDataValueComparerMock( $this->testDumpMetaInformation, $this->testDataValue, 'foo' );
    }


    /**
     * @dataProvider getComparerDataProvider
     */
    public function testComparer( $dataValue, $comparerClass )
    {
        $comparer = DataValueComparer::getComparer( $this->testDumpMetaInformation, $dataValue, $this->testExternalValues );
        if ( $comparerClass ) {
            $this->assertInstanceOf( $comparerClass, $comparer );
        } else {
            $this->assertNull( $comparer );
        }
    }

    /**
     * Test cases for testGetComparer
     * @return array
     */
    public function getComparerDataProvider()
    {
        return array(
            array(
                new EntityIdValue( new ItemId( 'Q42' ) ),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\EntityIdValueComparer'
            ),
            array(
                new MonolingualTextValue( 'en', 'foo' ),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer'
            ),
            array(
                new MultilingualTextValue( array( new MonolingualTextValue( 'en', 'foo' ) ) ),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer' ),
            array(
                new QuantityValue( new  DecimalValue( 42 ), '1', new DecimalValue( 42 ), new DecimalValue( 42 ) ),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer' ),
            array(
                new StringValue( 'foo' ),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer' ),
            array(
                new TimeValue( '+00000002013-12-07T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' ),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer' ),
            array(
                new GlobeCoordinateValue( new LatLongValue( 52.5, 13.3 ), 0.016 ),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer' ),
            array(
                new UnknownValue( null ),
                null )
        );
    }


    /**
     * Returns DataValueComparer mock with given arguments
     * @param DumpMetaInformation $dumpMetaInformation
     * @param DataValue $dataValue
     * @param array $externalValues
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDataValueComparerMock( $dumpMetaInformation, $dataValue, $externalValues )
    {
        return $this->getMockForAbstractClass(
            'WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer',
            array( $dumpMetaInformation, $dataValue, $externalValues )
        );
    }
}