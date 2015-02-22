<?php

namespace WikidataQuality\ExternalValidation\Api\Serializer;


use Wikibase\Lib\Serializers\SerializerObject;


/**
 * Class CompareResultSerializer
 * @package WikidataQuality\ExternalValidation\Api
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CompareResultSerializer extends SerializerObject {
    /**
     * @param \CompareResult $resultList
     */
    public function getSerialized( $result ) {
        // Create list of local values with indexed tag name
        $localValues = $result->getLocalValues();
        if( $localValues ) {
            $this->setIndexedTagName( $localValues, 'value' );
        }

        // Create list of external values with indexed tag name
        $externalValues = $result->getExternalValues();
        if( $externalValues ) {
            $this->setIndexedTagName( $externalValues, 'value' );
        }

        // Serialize whole CompareResult object
        $serialization = array(
            'propertyId' => (string)$result->getPropertyId(),
            'claimGuid' => $result->getClaimGuid(),
            'dataMismatch' => $result->hasDataMismatchOccurred(),
            'localValues' => $localValues,
            'externalValues' => $externalValues,
            'referencesMissing' => $result->areReferencesMissing(),
            'dataSourceName' => $result->getDataSourceName()
        );

        return $serialization;
    }
}