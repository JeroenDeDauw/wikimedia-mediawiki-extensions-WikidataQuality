<?php

namespace WikidataQuality\ExternalValidation\Tests\CrossCheck\Result;

use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;
use Wikibase\DataModel\Entity\PropertyId;
use DataValues\MonolingualTextValue;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult
 *
 * @author BP2014N1
 * @license GNU GPL v2+exte
 */
class CompareResultTest extends \MediaWikiTestCase {

    /**
     * @dataProvider constructValidArgumentsDataProvider
     */
    public function testConstructValidArguments( $propertyId, $claimGuid, $localValue, $externalValues, $dataMismatch, $dumpMetaInformation )
    {
        $compareResult = new CompareResult( $propertyId, $claimGuid, $localValue, $externalValues, $dataMismatch, $dumpMetaInformation );

        $this->assertEquals( $propertyId, $compareResult->getPropertyId() );
        $this->assertEquals( $claimGuid, $compareResult->getClaimGuid() );
        $this->assertEquals( $localValue, $compareResult->getLocalValue() );
        $this->assertEquals( $externalValues, $compareResult->getExternalValues() );
        $this->assertEquals( $dataMismatch, $compareResult->hasDataMismatchOccurred() );
        $this->assertEquals( $dataMismatch, $compareResult->hasDataMismatchOccurred() );
        $this->assertEquals( $dumpMetaInformation, $compareResult->getDumpMetaInformation() );
    }

    /**
     * Test cases for testConstructValidArguments
     * @return array
     */
    public function constructValidArgumentsDataProvider()
    {
        $propertyId = new PropertyId('P123');
        $claimGuid = '123456';
        $monolingualTextValue = new MonolingualTextValue( 'en', 'foo' );
        $dumpInformation = $this->getDumpMetaInformationMock();

        return array(
            array(
                $propertyId,
                $claimGuid,
                $monolingualTextValue,
                array( $monolingualTextValue ),
                true,
                $dumpInformation
            ),
            array(
                $propertyId,
                $claimGuid,
                $monolingualTextValue,
                array( $monolingualTextValue ),
                false,
                $dumpInformation
            ),
            array(
                $propertyId,
                $claimGuid,
                $monolingualTextValue,
                array( $monolingualTextValue, $monolingualTextValue ),
                true,
                $dumpInformation
            )
        );
    }

    /**
     * @dataProvider constructInvalidArgumentsDataProvider
     */
    public function testConstructInvalidArguments( $propertyId, $claimGuid, $localValue, $externalValues, $dataMismatch, $dumpMetaInformation )
    {
        $this->setExpectedException( 'InvalidArgumentException' );

        new CompareResult( $propertyId, $claimGuid, $localValue, $externalValues, $dataMismatch, $dumpMetaInformation );
    }

    /**
     * Test cases for testConstructInvalidArguments
     * @return array
     */
    public function constructInvalidArgumentsDataProvider()
    {
        $propertyId = new PropertyId('P123');
        $claimGuid = '123456';
        $monolingualTextValue = new MonolingualTextValue( 'en', 'foo' );
        $dumpInformation = $this->getDumpMetaInformationMock();
        $stringValue = 'foo';

        return array(
            array(
                'P123',
                $claimGuid,
                $monolingualTextValue,
                array( $monolingualTextValue ),
                true,
                $dumpInformation
            ),
            array(
                $propertyId,
                $claimGuid,
                $stringValue,
                array( $monolingualTextValue ),
                true,
                $dumpInformation
            ),
            array(
                $propertyId,
                $claimGuid,
                $monolingualTextValue,
                array( $stringValue ),
                true,
                $dumpInformation
            ),
            array(
                $propertyId,
                $claimGuid,
                $monolingualTextValue,
                $monolingualTextValue,
                true,
                $dumpInformation
            ),
            array(
                $propertyId,
                $claimGuid,
                $monolingualTextValue,
                array( $monolingualTextValue, $stringValue ),
                true,
                $dumpInformation
            )
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
 