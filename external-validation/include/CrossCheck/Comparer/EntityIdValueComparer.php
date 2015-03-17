<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use Wikibase\Repo\WikibaseRepo;


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
     * Starts the comparison of given EntityIdValue and values of external database.
     * @return bool - result of the comparison.
     */
    public function execute()
    {
        // Get terms of the references entity
        $entityId = $this->localValue->getEntityId();
        $terms = $this->getTerms( $entityId, $this->dumpMetaInformation->getLanguage() );

        // Compare value
        $result = false;
        if ( $this->externalValues && $terms && count( array_intersect( $terms, $this->externalValues ) ) > 0 ) {
            $result = true;
        }

        // Parse external values
        $this->parseExternalValues();

        return $result;
    }

    /**
     * Retrieves terms (label and aliases) of a given entity in the given language.
     * @param \EntityId $entityId
     * @param string $language
     * @return array
     */
    private function getTerms( $entityId, $language )
    {
        $entity = $this->getEntityLookup()->getEntity( $entityId );
        if ( $entity ) {
            $aliases = $entity->getAliases( $language );
            $label = $entity->getLabel( $language );

            $terms = $aliases;
            if ( $label != false ) {
                $terms[ ] = $label;
            }

            return $terms;
        }
    }

    /**
     * @param $entityId
     * @return \Wikibase\Lib\Store\EntityLookup|EntityLook
     * @codeCoverageIgnore
     */
    protected function getEntityLookup()
    {
        return WikibaseRepo::getDefaultInstance()->getEntityLookup();
    }
}