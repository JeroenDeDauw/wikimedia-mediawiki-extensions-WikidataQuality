<?php

namespace WikidataQuality\ExternalValidation\Api\Serializer;


use DataValues\Serializers\DataValueSerializer;
use Wikibase\Lib\Serializers\SerializerObject;


/**
 * Class CompareResultSerializer
 * @package WikidataQuality\ExternalValidation\Api
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CompareResultSerializer extends SerializerObject
{
    private $dataValueSerializer;


    public function __construct()
    {
        parent::__construct();

        // Get data value serializer
        $this->dataValueSerializer = new DataValueSerializer();
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
        $serialization = array(
            'propertyId' => (string)$result->getPropertyId(),
            'claimGuid' => $result->getClaimGuid(),
            'dataMismatch' => $result->hasDataMismatchOccurred(),
            'localValue' => $localValue,
            'externalValues' => $externalValues,
            'referencesMissing' => $result->areReferencesMissing(),
            'dataSourceName' => $result->getDataSourceName()
        );

        return $serialization;
    }
}