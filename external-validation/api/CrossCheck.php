<?php

namespace WikidataQuality\ExternalValidation\Api;


use ApiMain;
use Wikibase\Api\ApiWikibase;
use Wikibase\Lib\Serializers\SerializationOptions;
use WikidataQuality\ExternalValidation\CrossCheck\CrossChecker;
use WikidataQuality\ExternalValidation\Api\Serializer\CompareResultListSerializer;


/**
 * Class CrossCheck
 * @package WikidataQuality\ExternalValidation\Api
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CrossCheck extends ApiWikibase
{
    /**
     * @param ApiMain $main
     * @param string $name
     * @param string $prefix
     */
    public function __construct( ApiMain $main, $name, $prefix = '' )
    {
        parent::__construct( $main, $name, $prefix );
    }

    /**
     * Evaluates the parameters, runs the requested crosscheck, and sets up the result.
     */
    public function execute()
    {
        // Get parameters
        $params = $this->extractRequestParams();

        // Run cross-checker for each given entity
        $resultLists = array();
        foreach ( $params[ 'entities' ] as $id ) {
            $entityId = $this->getIdParser()->parse( $id );
            $crossChecker = new CrossChecker();
            $resultLists[ (string)$entityId ] = $crossChecker->execute( $entityId );
        }

        // Write results to output
        $output = $this->buildResultOutput( $resultLists );
        $this->getResult()->addValue( null, 'results', $output );

        // Mark success
        $this->getResultBuilder()->markSuccess( 1 );
    }

    /**
     * Build output for CompareResultList
     * @param $resultLists
     * @return array
     */
    private function buildResultOutput( $resultLists ) {
        // Initialize serializer
        $serializationOptions = array(
            SerializationOptions::OPT_INDEX_TAGS => $this->getResult()->getIsRawMode()
        );
        $serializer = new CompareResultListSerializer( new SerializationOptions( $serializationOptions ) );

        // Write output array
        $output = array();
        foreach( $resultLists as $entityId => $resultList ) {
            // Serialize CompareResultList
            $serializedResultList = $serializer->getSerialized( $resultList );

            // Add entity id depending on raw mode
            if ( $this->getResult()->getIsRawMode() ) {
                $output[ ] = array_merge(
                    array( 'id' => (string)$entityId ),
                    $serializedResultList
                );
            } else {
                $output[ (string)$entityId ] = $serializedResultList;
            }
        }

        // Set index tag for entities
        $this->getResult()->setIndexedTagName( $output, 'entity' );

        return $output;
    }

    /**
     * Returns an array of allowed parameters.
     * @return array
     */
    public function getAllowedParams()
    {
        return array_merge( parent::getAllowedParams(), array(
            'entities' => array(
                ApiWikibase::PARAM_TYPE => 'string',
                ApiWikibase::PARAM_ISMULTI => true,
                ApiWikibase::PARAM_REQUIRED => true
            )
        ) );
    }

    /**
     * Returns usage examples for this module.
     * @return array
     */
    public function getExamplesMessages()
    {
        return array(
            'action=wdqcrosscheck&entities=Q76' => 'apihelp-wdqcrosscheck-examples-1',
            'action=wdqcrosscheck&entities=Q76|Q567' => 'apihelp-wdqcrosscheck-examples-2'
        );
    }
}