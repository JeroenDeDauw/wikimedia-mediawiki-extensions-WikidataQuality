<?php

namespace WikidataQuality\ExternalValidation\Tests\CrossCheck\Comparer;


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
use WikidataQuality\ExternalValidation\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class DataValueComparerTest extends \MediaWikiTestCase
{
    /**
     * @dataProvider constructValidArgumentsDataProvider
     */
    public function testConstructValidArguments( $dumpMetaInformation, $localValue, $externalValues )
    {
        $comparerMock = $this->getDataValueComparerMock( $dumpMetaInformation, $localValue, $externalValues );

        $this->assertEquals( $dumpMetaInformation, $comparerMock->getDumpMetaInformation() );
        $this->assertEquals( $localValue, $comparerMock->getLocalValue() );
        $this->assertEquals( $externalValues, $comparerMock->getExternalValues() );
    }

    /**
     * Test cases for testConstructValidArguments
     * @return array
     */
    public function constructValidArgumentsDataProvider()
    {
        return array(
            array(
                $this->getDumpMetaInformationMock(),
                new StringValue( 'foo' ),
                array( 'foo', 'bar' )
            )
        );
    }


    /**
     * @dataProvider constructInvalidArgumentsDataProvider
     */
    public function testConstructInvalidArguments( $dumpMetaInformation, $localValue, $externalValues )
    {
        $this->setExpectedException( 'InvalidArgumentException' );

        $this->getDataValueComparerMock( $dumpMetaInformation, $localValue, $externalValues );
    }

    /**
     * Test cases for testConstructInvalidArguments
     * @return array
     */
    public function constructInvalidArgumentsDataProvider()
    {
        $dumpMetaInformation = $this->getDumpMetaInformationMock();
        $localValue = new StringValue( 'foo' );

        return array(
            array(
                $dumpMetaInformation,
                $localValue,
                'foo'
            ),
            array(
                $dumpMetaInformation,
                $localValue,
                42
            )
        );
    }


    /**
     * @dataProvider getComparerDataProvider
     */
    public function testGetComparer( $dumpMetaInformation, $localValue, $externalValues, $comparerClass )
    {
        $comparer = DataValueComparer::getComparer( $dumpMetaInformation, $localValue, $externalValues );
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
        $dumpMetaInformation = $this->getDumpMetaInformationMock();

        return array(
            array(
                $dumpMetaInformation,
                new EntityIdValue( new ItemId( 'Q42' ) ),
                array(),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\EntityIdValueComparer'
            ),
            array(
                $dumpMetaInformation,
                new MonolingualTextValue( 'en', 'foo' ),
                array(),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer'
            ),
            array(
                $dumpMetaInformation,
                new MultilingualTextValue( array( new MonolingualTextValue( 'en', 'foo' ) ) ),
                array(),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer'
            ),
            array(
                $dumpMetaInformation,
                QuantityValue::newFromNumber( 42, '1' ),
                array(),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer'
            ),
            array(
                $dumpMetaInformation,
                new StringValue( 'foo' ),
                array(),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer'
            ),
            array(
                $dumpMetaInformation,
                new TimeValue( '+00000002013-12-07T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' ),
                array(),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer'
            ),
            array(
                $dumpMetaInformation,
                new GlobeCoordinateValue( new LatLongValue( 52.5, 13.3 ), 0.016 ),
                array(),
                'WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer'
            ),
            array(
                $dumpMetaInformation,
                new UnknownValue( null ),
                array(),
                null
            )
        );
    }


    /**
     * Returns DataValueComparer mock with given arguments
     * @param DumpMetaInformation $dumpMetaInformation
     * @param DataValue $localValue
     * @param array $externalValues
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDataValueComparerMock( $dumpMetaInformation, $localValue, $externalValues )
    {
        return $this->getMockForAbstractClass(
            'WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer',
            array( $dumpMetaInformation, $localValue, $externalValues )
        );
    }

    /**
     * Returns DumpMetaInformation mock.
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getDumpMetaInformationMock()
    {
        $mock = $this->getMockBuilder( 'WikidataQuality\ExternalValidation\DumpMetaInformation' )
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}