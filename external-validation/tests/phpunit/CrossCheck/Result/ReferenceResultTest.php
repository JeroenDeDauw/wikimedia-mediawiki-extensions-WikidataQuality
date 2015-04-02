<?php

namespace WikidataQuality\ExternalValidation\Tests\CrossCheck\Result;

use DataValues\StringValue;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;
use WikidataQuality\ExternalValidation\CrossCheck\Result\ReferenceResult;
use Wikibase\DataModel\Entity\PropertyId;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Result\ReferenceResult
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResult
 *
 * @author BP2014N1
 * @license GNU GPL v2+exte
 */
class ReferenceResultTest extends \MediaWikiTestCase
{

    /**
     * @dataProvider constructValidArgumentsDataProvider
     */
    public function testConstructValidArguments( $referenceMissing, $addableReference )
    {
        $referenceResult = new ReferenceResult( $referenceMissing, $addableReference );

        $this->assertEquals( $referenceMissing, $referenceResult->areReferencesMissing() );
        $this->assertEquals( $addableReference, $referenceResult->getAddableReference() );
    }

    /**
     * Test cases for testConstructValidArguments
     * @return array
     */
    public function constructValidArgumentsDataProvider()
    {
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

        return array(
            array(
                true,
                $addableReference
            ),
            array(
                false,
                $addableReference
            )
        );
    }

    /**
     * @dataProvider constructInvalidArgumentsDataProvider
     */
    public function testConstructInvalidArguments( $referenceMissing, $addableReference, $expectedException )
    {
        $this->setExpectedException( $expectedException );
        new ReferenceResult( $referenceMissing, $addableReference );
    }

    /**
     * Test cases for testConstructInvalidArguments
     * @return array
     */
    public function constructInvalidArgumentsDataProvider()
    {
        return array(
            array(
                'foo',
                $this->getMock( 'Wikibase\DataModel\Reference' ),
                'InvalidArgumentException'
            ),
            array(
                true,
                'bar',
                'PHPUnit_Framework_Error'
            ),
            array(
                'foo',
                'bar',
                'PHPUnit_Framework_Error'
            )
        );
    }
}
 