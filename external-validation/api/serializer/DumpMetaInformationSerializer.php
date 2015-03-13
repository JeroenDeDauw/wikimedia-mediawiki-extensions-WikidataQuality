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
        $serialization = array(
            'sourceItemId' => $dumpMetaInformation->getSourceItemId()->getNumericId(),
            'language' => $dumpMetaInformation->getLanguage(),
            'sourceUrl' => $dumpMetaInformation->getSourceUrl(),
            'size' => $dumpMetaInformation->getSize(),
            'license' => $dumpMetaInformation->getLicense()
        );

        return $serialization;
    }
}