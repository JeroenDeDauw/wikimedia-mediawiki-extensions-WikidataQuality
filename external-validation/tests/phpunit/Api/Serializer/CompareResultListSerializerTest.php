<?php

namespace WikidataQuality\ExternalValidation\Tests\Api\Serializer;


use DataValues\StringValue;
use Wikibase\DataModel\Entity\PropertyId;
use DataValues\Serializers\DataValueSerializer;
use Wikibase\Lib\Serializers\SerializationOptions;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResultList;
use WikidataQuality\ExternalValidation\Api\Serializer\CompareResultSerializer;
use WikidataQuality\ExternalValidation\Api\Serializer\CompareResultListSerializer;


/**
 * @covers WikidataQuality\ExternalValidation\Api\Serializer\CompareResultListSerializer
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResultList
 * @uses   WikidataQuality\ExternalValidation\Api\Serializer\CompareResultSerializer
 * @uses   WikidataQuality\ExternalValidation\Api\Serializer\DumpMetaInformationSerializer
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CompareResultListSerializerTest extends \MediaWikiTestCase
{
    /**
     * Test compare result list
     * @var CompareResultList
     */
    protected $compareResultList;


    protected function setUp()
    {
        parent::setUp();

        // Create test compare result list
        $dumpMetaInformation = new DumpMetaInformation(
            '1',
            '36578',
            new \DateTime( '2015-01-01 00:00:00' ),
            'en',
            'http://www.foo.bar',
            42,
            'CC0'
        );

        $this->compareResultList = new CompareResultList(
            array(
                new CompareResult(
                    new PropertyId( 'P1' ),
                    'Q1$92581263-f783-45b8-9d0d-2b6be21db093',
                    new StringValue( 'foo' ),
                    array( new StringValue( 'bar' ) ),
                    true,
                    true,
                    $dumpMetaInformation
                ),
                new CompareResult(
                    new PropertyId( 'P1' ),
                    'Q1$bb94754e-15a2-4b26-8e83-8fca52276a97',
                    new StringValue( 'foo' ),
                    array( new StringValue( 'foo' ) ),
                    false,
                    true,
                    $dumpMetaInformation
                ),
                new CompareResult(
                    new PropertyId( 'P2' ),
                    'Q1$4cea8d76-a8a7-4921-ab18-d370e41ab6bf',
                    new StringValue( 'foo' ),
                    array( new StringValue( 'bar' ) ),
                    true,
                    true,
                    $dumpMetaInformation
                )
            )
        );
    }

    protected function tearDown()
    {
        unset( $this->compareResultList );
        parent::tearDown();
    }


    public function testGetSerialized()
    {
        // Get serializer
        $compareResultSerializer = new CompareResultSerializer();
        $compareResultListSerializer = new CompareResultListSerializer();

        // Get serialization of compare result
        $result = $compareResultListSerializer->getSerialized( $this->compareResultList );

        // Run assertions
        foreach ( $this->compareResultList->getPropertyIds() as $propertyId ) {
            foreach ( $this->compareResultList->getWithPropertyId( $propertyId ) as $compareResult ) {
                $this->assertContains( $compareResultSerializer->getSerialized( $compareResult ), $result[ (string)$propertyId ] );
            }
        }
    }

    public function testGetSerializedIndexTags()
    {
        // Get serializer
        $options = new SerializationOptions();
        $options->setIndexTags( true );
        $compareResultSerializer = new CompareResultSerializer( $options );
        $compareResultListSerializer = new CompareResultListSerializer( $options );

        // Get serialization of compare result
        $serializedResultList = $compareResultListSerializer->getSerialized( $this->compareResultList );

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
                foreach ( $this->compareResultList->getWithPropertyId( $propertyId ) as $compareResult ) {
                    $this->assertContains( $compareResultSerializer->getSerialized( $compareResult ), $serializedResultsPerProperty );
                }

                // Check indexed tag for results
                $this->assertArrayHasKey( '_element', $serializedResultsPerProperty );
                $this->assertEquals( 'result', $serializedResultsPerProperty['_element'] );
            }
        }

    }
}
