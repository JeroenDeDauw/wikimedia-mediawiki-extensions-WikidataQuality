<?php

namespace WikidataQuality\ExternalValidation\Tests\Api\Serializer;


use DataValues\StringValue;
use Wikibase\DataModel\Entity\PropertyId;
use DataValues\Serializers\DataValueSerializer;
use Wikibase\Lib\Serializers\SerializationOptions;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;
use WikidataQuality\ExternalValidation\Api\Serializer\DumpMetaInformationSerializer;
use WikidataQuality\ExternalValidation\Api\Serializer\CompareResultSerializer;

/**
 * @covers WikidataQuality\ExternalValidation\Api\Serializer\CompareResultSerializer
 *
 * @uses   DataValues\Serializers\DataValueSerializer
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult
 * @uses   WikidataQuality\ExternalValidation\Api\Serializer\DumpMetaInformationSerializer
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CompareResultSerializerTest extends \MediaWikiTestCase
{
    /**
     * @dataProvider getSerializedDataProvider
     */
    public function testGetSerialized( $compareResult, $shouldIndexTags )
    {
        // Get serializer
        $options = new SerializationOptions();
        $options->setIndexTags( $shouldIndexTags );
        $dataValueSerializer = new DataValueSerializer( $options );
        $dumpMetaInformationSerializer = new DumpMetaInformationSerializer( $options );
        $compareResultSerializer = new CompareResultSerializer( $options );

        // Get serialization of compare result
        $result = $compareResultSerializer->getSerialized( $compareResult );

        // Run assertions
        $this->assertEquals( $compareResult->getPropertyId(), $result[ 'propertyId' ] );
        $this->assertEquals( $compareResult->getClaimGuid(), $result[ 'claimGuid' ] );
        $this->assertEquals( $compareResult->areReferencesMissing(), $result[ 'referencesMissing' ] );
        $this->assertEquals( $dataValueSerializer->serialize( $compareResult->getLocalValue() ), $result[ 'localValue' ] );
        $this->assertEquals( $dumpMetaInformationSerializer->getSerialized( $compareResult->getDumpMetaInformation() ), $result[ 'dataSource' ] );

        if ( $compareResult->hasDataMismatchOccurred() ) {
            $this->assertEquals( 'mismatch', $result[ 'result' ] );
        } else {
            $this->assertEquals( 'match', $result[ 'result' ] );
        }

        foreach ( $compareResult->getExternalValues() as $externalValue ) {
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
            '1',
            '36578',
            new \DateTime( '2015-01-01 00:00:00' ),
            'en',
            'http://www.foo.bar',
            42,
            'CC0'
        );

        return array(
            // Data mismatch and no indexed tags
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
                false
            ),
            // Data match and indexed tags
            array (
                new CompareResult(
                    new PropertyId( 'P1' ),
                    'Q1$92581263-f783-45b8-9d0d-2b6be21db093',
                    new StringValue( 'foo' ),
                    array(
                        new StringValue( 'foo' ),
                        new StringValue( 'bar' )
                    ),
                    false,
                    true,
                    $dumpMetaInformation
                ),
                true
            )
        );
    }
}
