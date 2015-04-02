<?php

namespace WikidataQuality\ExternalValidation\CrossCheck;

use DataValues\StringValue;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;
use WikidataQuality\ExternalValidation\CrossCheck\Result\ReferenceResult;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;

/**
 * Class ReferenceHandler
 * @package WikidataQuality\ExternalValidation\CrossCheck
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class ReferenceHandler
{

    const STATED_IN_PID = 248;

    private $statement;
    private $compareResult;
    private $externalIds;
    private $identifierPropertyId;
    private $addReferencesAutomatically;

    function __construct( $statement, CompareResult $compareResult, $externalIds, $identifierPropertyId, $addReferencesAutomatically = false )
    {
        $this->statement = $statement;
        $this->compareResult = $compareResult;
        $this->externalIds = $externalIds;
        $this->identifierPropertyId = $identifierPropertyId;
        $this->addReferencesAutomatically = $addReferencesAutomatically;
    }

    public function execute()
    {
        $references = array();
        foreach ( $this->statement->getReferences() as $reference ) {
            foreach ( $reference->getSnaks() as $referenceSnak ) {
                if ( $referenceSnak )
                    $references[ ] = $referenceSnak;
            }
        }

        return $this->evaluateReferences( $references );
    }

    private function evaluateReferences( $references )
    {
        $sourceItemId = $this->compareResult->getDumpMetaInformation()->getSourceItemId();
        $statedInSnak = new PropertyValueSnak( new PropertyId( 'P' . self::STATED_IN_PID ), new EntityIdValue( $sourceItemId ) );

        $addableReferenceSnaks = new SnakList();
        $addableReferenceSnaks->addSnak( $statedInSnak );

        foreach ( $this->externalIds as $externalId ) {
            $withIdSnak = new PropertyValueSnak( $this->identifierPropertyId, new StringValue( $externalId ) );
            $addableReferenceSnaks->addSnak( $withIdSnak );
        }

        // TODO: test if working (probably not) ...
        // TODO: think about implementation: currently only adds first reference (maybe ranks of refs?)
        if ( count( $references ) == 0 ) {
            if ( $this->addReferencesAutomatically ) {
                if ( !$this->compareResult->hasDataMismatchOccurred() ) {
                    // $this->statement->addNewReference( $statedInSnak );
                    // $this->statement->addNewReference( $withIdSnak );
                }
            }
            $referenceMissing = true;
        } else {
            $referenceMissing = false;
        }

        $addableReference = new Reference( $addableReferenceSnaks );
        return new ReferenceResult( $referenceMissing, $addableReference );
    }
}