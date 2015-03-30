<?php

namespace WikidataQuality\ExternalValidation\Tests\Api\Serializer;


use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use WikidataQuality\ExternalValidation\Api\Serializer\DumpMetaInformationSerializer;

/**
 * @covers WikidataQuality\ExternalValidation\Api\Serializer\DumpMetaInformationSerializer
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class DumpMetaInformationSerializerTest extends \MediaWikiTestCase {
    /**
     * @var DumpMetaInformation
     */
    private $dumpMetaInformation;


    protected function setUp()
    {
        parent::setUp();

        // Create dump meta information
        $this->dumpMetaInformation = new DumpMetaInformation(
            new ItemId( 'Q36578' ),
            new \DateTime( '2015-01-01 00:00:00' ),
            'en',
            'http://www.foo.bar',
            42,
            'CC0' );
    }


    public function testGetSerialized()
    {
        $serializer = new DumpMetaInformationSerializer();
        $result = $serializer->getSerialized($this->dumpMetaInformation);

        $this->assertEquals($this->dumpMetaInformation->getSourceItemId()->getSerialization(), $result['sourceItemId']);
        $this->assertEquals($this->dumpMetaInformation->getLanguage(), $result['language']);
        $this->assertEquals($this->dumpMetaInformation->getSourceUrl(), $result['sourceUrl']);
        $this->assertEquals($this->dumpMetaInformation->getSize(), $result['size']);
        $this->assertEquals($this->dumpMetaInformation->getLicense(), $result['license']);
    }
}
