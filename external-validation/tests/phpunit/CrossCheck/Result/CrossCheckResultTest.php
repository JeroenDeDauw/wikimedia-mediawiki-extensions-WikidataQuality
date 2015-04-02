<?php

namespace WikidataQuality\ExternalValidation\Tests\CrossCheck\Result;
use DataValues\StringValue;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;
use WikidataQuality\ExternalValidation\CrossCheck\Result\ReferenceResult;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResult;
use Wikibase\DataModel\Reference;

/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResult
 *
 * @uses WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult
 * @uses WikidataQuality\ExternalValidation\CrossCheck\Result\ReferenceResult
 *
 * @author BP2014N1
 * @license GNU GPL v2+exte
 */

class CrossCheckResultTest extends \PHPUnit_Framework_TestCase
{
    private $compareResult;
    private $referenceResult;

    protected function setUp()
    {
        parent::setUp();

        // set up compare result

        $propertyId = new PropertyId('P123');
        $claimGuid = '123456';
        $externalValue = new StringValue( 'foo' );
        $dumpInformation = $this->getDumpMetaInformationMock();

        $this->compareResult = new CompareResult(
            $propertyId,
            $claimGuid,
            $externalValue,
            array( $externalValue ),
            false,
            $dumpInformation
        );

        // set up reference result

        $statedInPid = new PropertyId( 'P248' );
        $sourceItemId = new ItemId( 'Q36578' );
        $statedInSnak = new PropertyValueSnak( $statedInPid, new EntityIdValue( $sourceItemId ) );

        $identifierPropertyId = new PropertyId( 'P277' );
        $externalId = '';
        $withIdSnak = new PropertyValueSnak( $identifierPropertyId, new StringValue( $externalId ) );

        $addableReferenceSnaks = new SnakList();
        $addableReferenceSnaks->addSnak( $statedInSnak );
        $addableReferenceSnaks->addSnak( $withIdSnak );

        $addableReference = new Reference( $addableReferenceSnaks );

        $this->referenceResult = new ReferenceResult(
            true,
            $addableReference
        );
    }


    public function testConstructValidArguments()
    {
        $crossCheckResult = new CrossCheckResult( $this->compareResult, $this->referenceResult );

        $this->assertEquals( $this->compareResult, $crossCheckResult->getCompareResult() );
        $this->assertEquals( $this->referenceResult, $crossCheckResult->getReferenceResult() );
    }

    /**
     * @dataProvider constructInvalidArgumentsDataProvider
     */
    public function testConstructInvalidArguments( $compareResult, $referenceResult )
    {
        $this->setExpectedException( 'PHPUnit_Framework_Error' );
        new CrossCheckResult( $compareResult, $referenceResult );
    }

    /**
     * Test cases for testConstructInvalidArguments
     * @return array
     */
    public function constructInvalidArgumentsDataProvider()
    {
        return array(
            array(
                $this->compareResult,
                'bar'
            ),
            array(
                'foo',
                $this->referenceResult
            ),
            array(
                'foo',
                'bar'
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
