<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use Wikibase\Repo\WikibaseRepo;
use Wikibase\DataModel\Entity\EntityIdValue;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;


/**
 * Class EntityIdValueComparer
 * @package WikidataQuality\ExternalValidation\CrossCheck\Comparer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class EntityIdValueComparer extends DataValueComparer
{
    /**
     * Array of DataValue classes that are supported by the current comparer.
     * @var array
     */
    public static $acceptedDataValues = array( 'Wikibase\DataModel\Entity\EntityIdValue' );


    /**
     * @param EntityIdValue $dataValue
     * @param array $externalValues
     */
    public function __construct( EntityIdValue $dataValue, $externalValues )
    {
        parent::__construct( $dataValue, $externalValues );
    }


    /**
     * Starts the comparison of given EntityIdValue and values of external database.
     * @return \CompareResult - result of the comparison.
     */
    public function execute()
    {
        // Get terms of the references entity
        $entityId = $this->dataValue->getEntityId();
        $this->localValues = $this->getTerms( $entityId, "de" ); //TODO: get from database

        // Compare value
        if ( $this->localValues && count( array_intersect($this->localValues, $this->externalValues) ) > 0 ) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * Retrieves terms (label and aliases) of a given entity in the given language.
     * @param \EntityId $entityId
     * @param string $language
     * @return array
     */
    private function getTerms( $entityId, $language )
    {
        $lookup = WikibaseRepo::getDefaultInstance()->getStore()->getEntityLookup();
        $entity = $lookup->getEntity( $entityId );
        if ( $entity ) {
            $aliases = $entity->getAliases( $language );
            $label = $entity->getLabel( $language );

            $terms = $aliases;
            $terms[ ] = $label;

            return $terms;
        }
    }
}