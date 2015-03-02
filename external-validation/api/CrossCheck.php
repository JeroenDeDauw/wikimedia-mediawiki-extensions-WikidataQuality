<?php

namespace WikidataQuality\ExternalValidation\Api;


use ApiMain;
use Wikibase\Api\ApiWikibase;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\DataModel\Statement\StatementList;
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
     * Wikibase entity id parser.
     * @var \Wikibase\DataModel\Entity\EntityIdParser
     */
    private $entityIdParser;

    /**
     * Wikibase entity lookup.
     * @var \Wikibase\Lib\Store\EntityLookup
     */
    private $entityLookup;

    /**
     * Wikibase claim guid parser.
     * @var \Wikibase\DataModel\Claim\ClaimGuidParser
     */
    private $claimGuidParser;

    /**
     * Wikibase clam guid validator.
     * @var \Wikibase\Lib\ClaimGuidValidator
     */
    private $claimGuidValidator;


    /**
     * @param ApiMain $main
     * @param string $name
     * @param string $prefix
     */
    public function __construct( ApiMain $main, $name, $prefix = '' )
    {
        parent::__construct( $main, $name, $prefix );

        $this->entityIdParser = WikibaseRepo::getDefaultInstance()->getEntityIdParser();
        $this->entityLookup = WikibaseRepo::getDefaultInstance()->getEntityLookup();
        $this->claimGuidParser = WikibaseRepo::getDefaultInstance()->getClaimGuidParser();
        $this->claimGuidValidator = WikibaseRepo::getDefaultInstance()->getClaimGuidValidator();
    }

    /**
     * Evaluates the parameters, runs the requested crosscheck, and sets up the result.
     */
    public function execute()
    {
        // Get parameters
        $params = $this->extractRequestParams();

        // Run cross-check
        if ( $params[ 'entities' ] && $params[ 'claims' ] ) {
            $this->dieError(
                'Either provide the ids of entities or ids of claims, that should be cross-checked.',
                'param-invalid'
            );
        }
        elseif ( $params[ 'entities' ] ) {
            $resultLists = $this->crossCheckEntities( $params[ 'entities' ], $params[ 'properties' ] );
        }
        elseif ( $params[ 'claims' ] ) {
            $resultLists = $this->crossCheckClaim( $params[ 'claims' ] );
        }
        else {
            $this->dieError(
                'Either provide the ids of entities or ids of claims, that should be cross-checked.',
                'param-missing'
            );
        }

        // Print result lists
        $this->writeResultOutput( $resultLists );
    }

    /**
     * Runs cross-check for specified entites.
     * @param array $entityIds - List of entity ids, that should be cross-checked
     * @param array|null $propertyIds - If specified, only statements with given property ids will be cross-checked.
     */
    private function crossCheckEntities( $entityIds, $propertyIds = null )
    {
        // Parse property ids
        if( $propertyIds ) {
            foreach( $propertyIds as $key => $propertyId ) {
                $propertyIds[ $key ] = $this->entityIdParser->parse( $propertyId );
            }
        }

        $resultLists = array();
        foreach ( $entityIds as $entityId ) {
            // Get entity from id
            $entity = $this->entityLookup->getEntity( $this->getIdParser()->parse( $entityId ) );

            // Run cross-check
            $crossChecker = new CrossChecker();
            $resultLists[ (string)$entityId ] = $crossChecker->crossCheckEntity( $entity, $propertyIds );
        }

        return $resultLists;
    }

    /**
     * Runs cross-check for specified claims.
     * @param array $claimGuids - List of guids of claims, that should be cross-checked
     */
    private function crossCheckClaim( $claimGuids )
    {
        // Group claim guids by entity
        $groupedClaimGuids = array();
        foreach ( $claimGuids as $claimGuid ) {
            // Check if claim guid is valid
            if ( $this->claimGuidValidator->validateFormat( $claimGuid ) === false ) {
                $this->dieError( 'Invalid claim guid', 'invalid-guid' );
            }

            $claimGuid = $this->claimGuidParser->parse( $claimGuid );
            $groupedClaimGuids[ (string)$claimGuid->getEntityId() ][] = $claimGuid;
        }

        // Run cross-checker for each entity with corresponding claims
        $resultLists = array();
        foreach ( $groupedClaimGuids as $entityId => $claimGuidsPerEntity ) {
            // Get entity
            $entity = $this->entityLookup->getEntity( $this->entityIdParser->parse( $entityId ) );

            if( $entity ) {
                // Get statements of claims
                $statements = new StatementList();
                foreach ( $entity->getStatements() as $statement ) {
                    if ( in_array( $statement->getClaim()->getGuid(), $claimGuidsPerEntity ) ) {
                        $statements->addStatement( $statement );
                    }
                }

                // Run cross-check for filtered statements
                $crossChecker = new CrossChecker();
                $resultLists[ (string)$entityId ] = $crossChecker->crossCheckStatements( $entity, $statements );
            }
            else {
                $resultLists[ (string)$entityId ] = null;
            }
        }

        return $resultLists;
    }

    /**
     * Writes output for CompareResultList
     * @param $resultLists
     * @return array
     */
    private function writeResultOutput( $resultLists )
    {
        // Initialize serializer
        $serializationOptions = array(
            SerializationOptions::OPT_INDEX_TAGS => $this->getResult()->getIsRawMode()
        );
        $serializer = new CompareResultListSerializer( new SerializationOptions( $serializationOptions ) );

        // Write output array
        $output = array();
        foreach ( $resultLists as $entityId => $resultList ) {
            if( $resultList ) {
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
            else {
                // If resultList is null, entity does not exist
                $output[ (string)$entityId ] = array(
                    'missing' => ''
                );
            }

        }

        // Set index tag for entities
        $this->getResult()->setIndexedTagName( $output, 'entity' );

        // Write output
        $this->getResult()->addValue( null, 'results', $output );

        // Mark success
        $this->getResultBuilder()->markSuccess( 1 );
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
                ApiWikibase::PARAM_ISMULTI => true
            ),
            'properties' => array(
                ApiWikibase::PARAM_TYPE => 'string',
                ApiWikibase::PARAM_ISMULTI => true
            ),
            'claims' => array(
                ApiWikibase::PARAM_TYPE => 'string',
                ApiWikibase::PARAM_ISMULTI => true
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
            'action=wdqcrosscheck&entities=Q76|Q567' => 'apihelp-wdqcrosscheck-examples-2',
            'action=wdqcrosscheck&entities=Q76|Q567&properties=P19' => 'apihelp-wdqcrosscheck-examples-3',
            'action=wdqcrosscheck&entities=Q76|Q567&properties=P19|P31' => 'apihelp-wdqcrosscheck-examples-4',
            'action=wdqcrosscheck&claims=Q42$D8404CDA-25E4-4334-AF13-A3290BCD9C0F' => 'apihelp-wdqcrosscheck-examples-5'
        );
    }
}