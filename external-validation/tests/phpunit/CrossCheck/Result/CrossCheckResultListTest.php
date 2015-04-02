<?php

namespace WikidataQuality\ExternalValidation\Tests\CrossCheck\Result;

use DataValues\QuantityValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Lib\ClaimGuidGenerator;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResultList;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResult;
use WikidataQuality\ExternalValidation\CrossCheck\Result\ReferenceResult;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResultList
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResult
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\ReferenceResult
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CrossCheckResultListTest extends \PHPUnit_Framework_TestCase
{
    private $crossCheckResultTestList;
    private $anotherCrossCheckResultTestList;
    private $testCrossCheckResult;
    private $anotherTestCrossCheckResult;

    protected function setUp()
    {
        parent::setUp();
        $this->crossCheckResultTestList = new CrossCheckResultList( array(
            $this->buildCrossCheckResult( new PropertyId( 'P1' ), false, false ),
            $this->buildCrossCheckResult( new PropertyId( 'P2' ), false, false ),
            $this->buildCrossCheckResult( new PropertyId( 'P3' ), false, false )
        ) );
        $this->anotherCrossCheckResultTestList = new CrossCheckResultList( array(
            $this->buildCrossCheckResult( new PropertyId( 'P4' ), false, false ),
            $this->buildCrossCheckResult( new PropertyId( 'P5' ), false, false )
        ) );
        $this->testCrossCheckResult = $this->buildCrossCheckResult( new PropertyId( 'P5' ), true, false );
        $this->anotherTestCrossCheckResult = $this->buildCrossCheckResult( new PropertyId( 'P6' ), false, true );
    }

    protected function tearDown()
    {
        unset( $this->crossCheckResultTestList, $this->anotherCrossCheckResultTestList );
        parent::tearDown();
    }

    public function testCount()
    {
        $expected = 3;
        $actual = $this->crossCheckResultTestList->count();
        $this->assertEquals( $expected, $actual );

        $expected = 2;
        $actual = $this->anotherCrossCheckResultTestList->count();
        $this->assertEquals( $expected, $actual );
    }

    public function testAddingCrossCheckResults()
    {
        $count = $this->crossCheckResultTestList->count();
        $this->crossCheckResultTestList->add( $this->testCrossCheckResult );
        $expected = $count + 1;
        $actual = $this->crossCheckResultTestList->count();
        $this->assertEquals( $expected, $actual );
    }

    public function testMergingCrossCheckResultsLists()
    {
        $count = $this->crossCheckResultTestList->count();
        $anotherCount = $this->anotherCrossCheckResultTestList->count();
        $this->crossCheckResultTestList->merge($this->anotherCrossCheckResultTestList);
        $expected = $count + $anotherCount;
        $actual = $this->crossCheckResultTestList->count();
        $this->assertEquals($expected, $actual);
    }

    public function testDataMismatchOccurrence()
    {
        $expected = false;
        $actual = $this->anotherCrossCheckResultTestList->hasDataMismatchOccurred();
        $this->assertEquals($expected, $actual);

        $expected = true;
        $this->anotherCrossCheckResultTestList->add($this->testCrossCheckResult);
        $actual = $this->anotherCrossCheckResultTestList->hasDataMismatchOccurred();
        $this->assertEquals($expected, $actual);
    }

    public function testMissingReferences()
    {
        $expected = false;
        $actual = $this->anotherCrossCheckResultTestList->areReferencesMissing();
        $this->assertEquals($expected, $actual);

        $expected = true;
        $this->anotherCrossCheckResultTestList->add($this->anotherTestCrossCheckResult);
        $actual = $this->anotherCrossCheckResultTestList->areReferencesMissing();
        $this->assertEquals($expected, $actual);
    }

    public function testGetPropertyIds() {
        $expected = array( new PropertyId( 'P4' ), new PropertyId( 'P5' ) );
        $actual = $this->anotherCrossCheckResultTestList->getPropertyIds();
        $this->assertEquals( $expected, $actual );

        $this->anotherCrossCheckResultTestList->add($this->testCrossCheckResult);
        $actual = $this->anotherCrossCheckResultTestList->getPropertyIds();
        $this->assertEquals( $expected, $actual );
    }

    public function testGetWithPropertyId() {
        // test by counting CrossCheck Results
        $expected = 1;
        $actual = $this->anotherCrossCheckResultTestList->getWithPropertyId( new PropertyId( 'P5' ))->count();
        $this->assertEquals( $expected, $actual );

        $this->anotherCrossCheckResultTestList->add($this->testCrossCheckResult);
        $expected = 2;
        $actual = $this->anotherCrossCheckResultTestList->getWithPropertyId( new PropertyId( 'P5' ))->count();
        $this->assertEquals( $expected, $actual );
    }

    /**
     * Returns DumpMetaInformation mock.
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDumpMetaInformationMock()
    {
        $mock = $this->getMockBuilder( 'WikidataQuality\ExternalValidation\DumpMetaInformation' )
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * Generates CrossCheckResult
     * @return CrossCheckResult
     */
    private function buildCrossCheckResult( $propertyId, $dataMismatch, $referencesMissing )
    {
        $itemId = new ItemId( 'Q15' );
        $claimGuidGenerator = new ClaimGuidGenerator();
        $claimGuid = $claimGuidGenerator->newGuid( $itemId );
        $value = 17;
        $localValue = QuantityValue::newFromNumber( $value, '1' );
        if ( $dataMismatch ) $value = 14;
        $externalValues = array( QuantityValue::newFromNumber( $value, '1' ) );
        $dumpMetaInformation = $this->getDumpMetaInformationMock();
        $compareResult = new CompareResult( $propertyId, $claimGuid, $localValue, $externalValues, $dataMismatch, $dumpMetaInformation );

        $referenceResult = new ReferenceResult( $referencesMissing, $this->getMock( 'Wikibase\DataModel\Reference' ) );

        $crossCheckResult = new CrossCheckResult( $compareResult, $referenceResult );
        return $crossCheckResult;
    }
}