<?php

namespace WikidataQuality\ExternalValidation\Tests\Api\Serializer;


use DataValues\StringValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Lib\Serializers\SerializationOptions;
use WikidataQuality\ExternalValidation\CrossCheck\Result\ReferenceResult;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResult;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResultList;
use WikidataQuality\ExternalValidation\Api\Serializer\CrossCheckResultSerializer;
use WikidataQuality\ExternalValidation\Api\Serializer\CrossCheckResultListSerializer;


/**
 * @covers WikidataQuality\ExternalValidation\Api\Serializer\CrossCheckResultListSerializer
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResult
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\ReferenceResult
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResultList
 * @uses   WikidataQuality\ExternalValidation\Api\Serializer\CrossCheckResultSerializer
 * @uses   WikidataQuality\ExternalValidation\Api\Serializer\DumpMetaInformationSerializer
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CrossCheckResultListSerializerTest extends \MediaWikiTestCase
{
    /**
     * Test crossCheck result list
     * @var CrossCheckResultList
     */
    protected $crossCheckResultList;


    protected function setUp()
    {
        parent::setUp();

        // Create test crosscheck result list
        $dumpMetaInformation = new DumpMetaInformation(
            new ItemId( 'Q36578' ),
            new \DateTime( '2015-01-01 00:00:00' ),
            'en',
            'http://www.foo.bar',
            42,
            'CC0'
        );

        $this->crossCheckResultList = new CrossCheckResultList(
            array(
                new CrossCheckResult(
                    new CompareResult(
                        new PropertyId( 'P1' ),
                        'Q1$92581263-f783-45b8-9d0d-2b6be21db093',
                        new StringValue( 'foo' ),
                        array( new StringValue( 'bar' ) ),
                        true,
                        $dumpMetaInformation
                    ),
                    new ReferenceResult(
                        true,
                        $this->getMock( 'Wikibase\DataModel\Reference' )
                    )
                ),
                new CrossCheckResult(
                    new CompareResult(
                        new PropertyId( 'P1' ),
                        'Q1$bb94754e-15a2-4b26-8e83-8fca52276a97',
                        new StringValue( 'foo' ),
                        array( new StringValue( 'foo' ) ),
                        false,
                        $dumpMetaInformation
                    ),
                    new ReferenceResult(
                        true,
                        $this->getMock( 'Wikibase\DataModel\Reference' )
                    )
                ),
                new CrossCheckResult(
                    new CompareResult(
                        new PropertyId( 'P2' ),
                        'Q1$4cea8d76-a8a7-4921-ab18-d370e41ab6bf',
                        new StringValue( 'foo' ),
                        array( new StringValue( 'bar' ) ),
                        true,
                        $dumpMetaInformation
                    ),
                    new ReferenceResult(
                        true,
                        $this->getMock( 'Wikibase\DataModel\Reference' )
                    )
                )
            )
        );
    }

    protected function tearDown()
    {
        unset( $this->crossCheckResultList );
        parent::tearDown();
    }


    public function testGetSerialized()
    {
        // Get serializer
        $crossCheckResultSerializer = new CrossCheckResultSerializer();
        $crossCheckResultListSerializer = new CrossCheckResultListSerializer();

        // Get serialization of crosscheck result
        $result = $crossCheckResultListSerializer->getSerialized( $this->crossCheckResultList );

        // Run assertions
        foreach ( $this->crossCheckResultList->getPropertyIds() as $propertyId ) {
            foreach ( $this->crossCheckResultList->getWithPropertyId( $propertyId ) as $crossCheckResult ) {
                $this->assertContains( $crossCheckResultSerializer->getSerialized( $crossCheckResult ), $result[ (string)$propertyId ] );
            }
        }
    }

    public function testGetSerializedIndexTags()
    {
        // Get serializer
        $options = new SerializationOptions();
        $options->setIndexTags( true );
        $crossCheckResultSerializer = new CrossCheckResultSerializer( $options );
        $crossCheckResultListSerializer = new CrossCheckResultListSerializer( $options );

        // Get serialization of crosscheck result
        $serializedResultList = $crossCheckResultListSerializer->getSerialized( $this->crossCheckResultList );

        // Run assertions
        foreach ( $serializedResultList as $key => $serializedResultsPerProperty ) {
            if( $key === '_element' ) {
                // Check indexed tag
                $this->assertEquals( 'property', $serializedResultsPerProperty );
            }
            else {
                // Check result group for result group
                $this->assertArrayHasKey( 'id', $serializedResultsPerProperty );
                $propertyId = new PropertyId( $serializedResultsPerProperty[ 'id' ] );
                foreach ( $this->crossCheckResultList->getWithPropertyId( $propertyId ) as $crossCheckResult ) {
                    $this->assertContains( $crossCheckResultSerializer->getSerialized( $crossCheckResult ), $serializedResultsPerProperty );
                }

                // Check indexed tag for results
                $this->assertArrayHasKey( '_element', $serializedResultsPerProperty );
                $this->assertEquals( 'result', $serializedResultsPerProperty['_element'] );
            }
        }

    }
}
