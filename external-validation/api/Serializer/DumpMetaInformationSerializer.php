<?php

namespace WikidataQuality\ExternalValidation\Api\Serializer;

use Wikibase\Lib\Serializers\SerializerObject;


/**
 * Class DumpMetaInformationSerializer
 * @package WikidataQuality\ExternalValidation\Api\Serializer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class DumpMetaInformationSerializer extends SerializerObject
{

    /**
     * @param DumpMetaInformation $dumpMetaInformation
     */
    public function getSerialized( $dumpMetaInformation )
    {
        $sourceUrls = $dumpMetaInformation->getSourceUrls();
        $this->setIndexedTagName( $sourceUrls, 'url' );

        $serialization = array(
            'sourceItemId' => $dumpMetaInformation->getSourceItemId()->getSerialization(),
            'language' => $dumpMetaInformation->getLanguage(),
            'sourceUrls' => $sourceUrls,
            'size' => $dumpMetaInformation->getSize(),
            'license' => $dumpMetaInformation->getLicense()
        );

        return $serialization;
    }
}