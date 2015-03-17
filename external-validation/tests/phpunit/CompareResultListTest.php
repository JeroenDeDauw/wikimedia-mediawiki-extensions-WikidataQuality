<?php

namespace WikidataQuality\ExternalValidation\Tests;

use DataValues\QuantityValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Lib\ClaimGuidGenerator;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResultList;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResultList
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class DataValueComparerTest extends \PHPUnit_Framework_TestCase
{
    private $compareResultTestList;
    private $anotherCompareResultTestList;
    private $testCompareResult;
    private $anotherTestCompareResult;

    protected function setUp()
    {
        parent::setUp();
        $this->compareResultTestList = new CompareResultList( array(
            $this->buildCompareResult( new PropertyId( 'P1' ), false, false ),
            $this->buildCompareResult( new PropertyId( 'P2' ), false, false ),
            $this->buildCompareResult( new PropertyId( 'P3' ), false, false )
        ) );
        $this->anotherCompareResultTestList = new CompareResultList( array(
            $this->buildCompareResult( new PropertyId( 'P4' ), false, false ),
            $this->buildCompareResult( new PropertyId( 'P5' ), false, false )
        ) );
        $this->testCompareResult = $this->buildCompareResult( new PropertyId( 'P5' ), true, false );
        $this->anotherTestCompareResult = $this->buildCompareResult( new PropertyId( 'P6' ), false, true );
    }

    protected function tearDown()
    {
        unset( $this->compareResultTestList, $this->anotherCompareResultTestList );
        parent::tearDown();
    }

    public function testCount()
    {
        $expected = 3;
        $actual = $this->compareResultTestList->count();
        $this->assertEquals( $expected, $actual );

        $expected = 2;
        $actual = $this->anotherCompareResultTestList->count();
        $this->assertEquals( $expected, $actual );
    }

    public function testAddingCompareResults()
    {
        $count = $this->compareResultTestList->count();
        $this->compareResultTestList->add( $this->testCompareResult );
        $expected = $count + 1;
        $actual = $this->compareResultTestList->count();
        $this->assertEquals( $expected, $actual );
    }

    public function testMergingCompareResultsLists()
    {
        $count = $this->compareResultTestList->count();
        $anotherCount = $this->anotherCompareResultTestList->count();
        $this->compareResultTestList->merge($this->anotherCompareResultTestList);
        $expected = $count + $anotherCount;
        $actual = $this->compareResultTestList->count();
        $this->assertEquals($expected, $actual);
    }

    public function testDataMismatchOccurrence()
    {
        $expected = false;
        $actual = $this->anotherCompareResultTestList->hasDataMismatchOccurred();
        $this->assertEquals($expected, $actual);

        $expected = true;
        $this->anotherCompareResultTestList->add($this->testCompareResult);
        $actual = $this->anotherCompareResultTestList->hasDataMismatchOccurred();
        $this->assertEquals($expected, $actual);
    }

    public function testMissingReferences()
    {
        $expected = false;
        $actual = $this->anotherCompareResultTestList->areReferencesMissing();
        $this->assertEquals($expected, $actual);

        $expected = true;
        $this->anotherCompareResultTestList->add($this->anotherTestCompareResult);
        $actual = $this->anotherTestCompareResult->areReferencesMissing();
        $this->assertEquals($expected, $actual);
    }

    public function testGetPropertyIds() {
        $expected = array( new PropertyId( 'P4' ), new PropertyId( 'P5' ) );
        $actual = $this->anotherCompareResultTestList->getPropertyIds();
        $this->assertEquals( $expected, $actual );

        $this->anotherCompareResultTestList->add($this->testCompareResult);
        $actual = $this->anotherCompareResultTestList->getPropertyIds();
        $this->assertEquals( $expected, $actual );
    }

    public function testGetWithPropertyId() {
        // test by counting Compare Results
        $expected = 1;
        $actual = $this->anotherCompareResultTestList->getWithPropertyId( new PropertyId( 'P5' ))->count();
        $this->assertEquals( $expected, $actual );

        $this->anotherCompareResultTestList->add($this->testCompareResult);
        $expected = 2;
        $actual = $this->anotherCompareResultTestList->getWithPropertyId( new PropertyId( 'P5' ))->count();
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
     * Generates CompareResult
     * @return CompareResult
     */
    private function buildCompareResult( $propertyId, $dataMismatch, $referencesMissing )
    {
        $itemId = new ItemId( 'Q15' );
        $claimGuidGenerator = new ClaimGuidGenerator();
        $claimGuid = $claimGuidGenerator->newGuid( $itemId );
        $value = 17;
        $localValue = QuantityValue::newFromNumber( $value, '1' );
        if ( $dataMismatch ) $value = 14;
        $externalValues = array( QuantityValue::newFromNumber( $value, '1' ) );
        $dumpMetaInformation = $this->getDumpMetaInformationMock();
        $compareResult = new CompareResult( $propertyId, $claimGuid, $localValue, $externalValues, $dataMismatch, $referencesMissing, $dumpMetaInformation );
        return $compareResult;
    }
}