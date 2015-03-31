<?php

namespace WikidataQuality\ExternalValidation\Api\Serializer;


use Wikibase\Lib\Serializers\SerializerObject;


/**
 * Class CrossCheckResultListSerializer
 * @package WikidataQuality\ExternalValidation\Api\Serializer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CrossCheckResultListSerializer extends SerializerObject
{
    private $compareResultSerializer;


    public function __construct( $options = null )
    {
        parent::__construct( $options );

        // Get compare result serializer
        $this->compareResultSerializer = new CrossCheckResultSerializer( $options );
    }


    /**
     * @param \CrossCheckResultList $resultList
     */
    public function getSerialized( $resultList )
    {
        $serialization = array();
        foreach ( $resultList->getPropertyIds() as $propertyId ) {
            // Index tags, if necessary
            if ( $this->getOptions()->shouldIndexTags() ) {
                $index = count( $serialization );
                $serialization[ $index ][ 'id' ] = (string)$propertyId;
                $this->setIndexedTagName( $serialization[ $index ], 'result' );
                $this->setIndexedTagName( $serialization, 'property' );
            } else {
                $index = (string)$propertyId;
            }

            // Serialize single CrossCheckResults
            foreach ( $resultList->getWithPropertyId( $propertyId ) as $result ) {
                $serialization[ $index ][ ] = $this->compareResultSerializer->getSerialized( $result );
            }
        }

        return $serialization;
    }
}