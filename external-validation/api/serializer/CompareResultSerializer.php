<?php

namespace WikidataQuality\ExternalValidation\Api\Serializer;


use DataValues\Serializers\DataValueSerializer;
use Wikibase\Lib\Serializers\SerializerObject;


/**
 * Class CompareResultSerializer
 * @package WikidataQuality\ExternalValidation\Api\Serializer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CompareResultSerializer extends SerializerObject
{
    /**
     * @var DataValueSerializer
     */
    private $dataValueSerializer;

    /**
     * @var DumpMetaInformationSerializer
     */
    private $dumpMetaInformationSerializer;


    public function __construct( $options = null )
    {
        parent::__construct( $options );

        // Get data value serializer
        $this->dataValueSerializer = new DataValueSerializer( $options );

        // Get dump meta information serializer
        $this->dumpMetaInformationSerializer = new DumpMetaInformationSerializer( $options );
    }


    /**
     * @param \CompareResult $resultList
     */
    public function getSerialized( $result )
    {
        // Serialize local value
        $localValue = $this->dataValueSerializer->serialize( $result->getLocalValue() );

        // Serialize external values
        $externalValues = array();
        if ( $result->getExternalValues() ) {
            foreach ( $result->getExternalValues() as $externalValue ) {
                $externalValues[ ] = $this->dataValueSerializer->serialize( $externalValue );
            }
            $this->setIndexedTagName( $externalValues, 'dataValue' );
        }

        // Serialize whole CompareResult object
        if ( $result->hasDataMismatchOccurred() ) {
            $dataMismatch = "mismatch";
        } else {
            $dataMismatch = "match";
        }

        $serialization = array(
            'propertyId' => (string)$result->getPropertyId(),
            'claimGuid' => $result->getClaimGuid(),
            'result' => $dataMismatch,
            'localValue' => $localValue,
            'externalValues' => $externalValues,
            'referencesMissing' => $result->areReferencesMissing(),
            'dataSource' => $this->dumpMetaInformationSerializer->getSerialized( $result->getDumpMetaInformation() )
        );

        return $serialization;
    }
}