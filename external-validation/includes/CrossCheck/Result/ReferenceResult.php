<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Result;

use DataValues\DataValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use Doctrine\Instantiator\Exception\InvalidArgumentException;


/**
 * Class ReferenceResult
 * @package WikidataQuality\ExternalValidation\CrossCheck\Result
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class ReferenceResult
{
    /**
     * Boolean that is true if there are no references
     * @var referenceMissing
     */
    private $referenceMissing;

    /**
     * Reference to an external db
     * @var Reference addableReference
     */
    private $addableReference;

    public function __construct( $referenceMissing, Reference $addableReference )
    {
        if ( is_bool( $referenceMissing ) ) {
            $this->referenceMissing = $referenceMissing;
        } else {
            throw new InvalidArgumentException( '$referenceMissing has to be boolean' );
        }
        $this->addableReference = $addableReference;
    }

    /**
     * @return bool
     */
    public function areReferencesMissing()
    {
        return $this->referenceMissing;
    }

    /**
     * @return Reference
     */
    public function getAddableReference()
    {
        return $this->addableReference;
    }
}