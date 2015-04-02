<?php

namespace WikidataQuality\ExternalValidation\Tests\Api\Serializer;


use DataValues\StringValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use DataValues\Serializers\DataValueSerializer;
use Wikibase\DataModel\Reference;
use Wikibase\Lib\Serializers\SerializationOptions;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;
use WikidataQuality\ExternalValidation\CrossCheck\Result\ReferenceResult;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResult;
use WikidataQuality\ExternalValidation\Api\Serializer\DumpMetaInformationSerializer;
use WikidataQuality\ExternalValidation\Api\Serializer\CrossCheckResultSerializer;

/**
 * @covers WikidataQuality\ExternalValidation\Api\Serializer\CrossCheckResultSerializer
 *
 * @uses   DataValues\Serializers\DataValueSerializer
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CrossCheckResult
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\ReferenceResult
 * @uses   WikidataQuality\ExternalValidation\Api\Serializer\DumpMetaInformationSerializer
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CrossCheckResultSerializerTest extends \MediaWikiTestCase
{
    /**
     * @dataProvider getSerializedDataProvider
     */
    public function testGetSerialized( $crossCheckResult, $shouldIndexTags )
    {
        // Get serializer
        $options = new SerializationOptions();
        $options->setIndexTags( $shouldIndexTags );
        $dataValueSerializer = new DataValueSerializer( $options );
        $dumpMetaInformationSerializer = new DumpMetaInformationSerializer( $options );
        $crossCheckResultSerializer = new CrossCheckResultSerializer( $options );

        // Get serialization of crosscheck result
        $result = $crossCheckResultSerializer->getSerialized( $crossCheckResult );

        // Run assertions
        $this->assertEquals( $crossCheckResult->getPropertyId(), $result[ 'propertyId' ] );
        $this->assertEquals( $crossCheckResult->getClaimGuid(), $result[ 'claimGuid' ] );
        $this->assertEquals( $crossCheckResult->areReferencesMissing(), $result[ 'referencesMissing' ] );
        $this->assertEquals( $dataValueSerializer->serialize( $crossCheckResult->getLocalValue() ), $result[ 'localValue' ] );
        $this->assertEquals( $dumpMetaInformationSerializer->getSerialized( $crossCheckResult->getDumpMetaInformation() ), $result[ 'dataSource' ] );

        if ( $crossCheckResult->hasDataMismatchOccurred() ) {
            $this->assertEquals( 'mismatch', $result[ 'result' ] );
        } else {
            $this->assertEquals( 'match', $result[ 'result' ] );
        }

        foreach ( $crossCheckResult->getExternalValues() as $externalValue ) {
            $this->assertContains( $dataValueSerializer->serialize( $externalValue ), $result[ 'externalValues' ] );
        }

        // Check indexed tag
        if( $options->shouldIndexTags() ) {
            $this->assertArrayHasKey( '_element', $result[ 'externalValues' ] );
            $this->assertContains( 'dataValue', $result[ 'externalValues' ][ '_element' ] );
        }
    }

    /**
     * Test cases for testGetSerialized
     * @return array
     */
    public function getSerializedDataProvider()
    {
        $dumpMetaInformation = new DumpMetaInformation(
            new ItemId( 'Q36578' ),
            new \DateTime( '2015-01-01 00:00:00' ),
            'en',
            'http://www.foo.bar',
            42,
            'CC0'
        );

        return array(
            // Data mismatch and no indexed tags
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
                false
            ),
            // Data match and indexed tags
            array(
                new CrossCheckResult(
                    new CompareResult(
                        new PropertyId( 'P1' ),
                        'Q1$92581263-f783-45b8-9d0d-2b6be21db093',
                        new StringValue( 'foo' ),
                        array(
                            new StringValue( 'foo' ),
                            new StringValue( 'bar' )
                        ),
                        false,
                        $dumpMetaInformation
                    ),
                    new ReferenceResult(
                        true,
                        $this->getMock( 'Wikibase\DataModel\Reference' )
                    )
                ),
                true
            )
        );
    }
}
