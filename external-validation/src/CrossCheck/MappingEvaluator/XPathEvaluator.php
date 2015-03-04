<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator;

use DOMNode;
use DOMXPath;
use DomDocument;

/**
 * Class XPathEvaluator
 * @package WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class XPathEvaluator extends MappingEvaluator
{
    /**
     * Array of data formats that can be evaluated with the current evaluator.
     * @var array
     */
    public static $acceptedDataFormats = array( "xml" );

    /**
     *
     * DOMXPath object used to evalute XPath expressions
     * @var DOMXPath
     */
    private $domXPath;


    /**
     * @param $externalData - external data object
     */
    public function __construct( $externalData )
    {
        parent::__construct( $externalData );

        $doc = new DomDocument();
        if ( !@$doc->loadXML( $this->externalData ) ) {
            throw new \InvalidArgumentException( '$externalData must be well-formed xml.' );
        }
        $this->domXPath = new DOMXPath( $doc );
    }


    /**
     * Evaluates a given query on external data object
     * @param string $nodeSelector - XPath expression to select concerning nodes
     * @param string $valueFormatter - optional XPath expression to re-format text of selected nodes
     */
    public function evaluate( $nodeSelector, $valueFormatter = null )
    {
        $evaluatedResult = array();

        $result = $this->domXPath->evaluate( $nodeSelector );
        foreach ( $result as $element ) {
            if ( $element instanceof DOMNode && !empty( $valueFormatter ) ) {
                $evaluatedResult[ ] = $this->domXPath->evaluate( $valueFormatter, $element );
            } else {
                $evaluatedResult[ ] = $element->textContent;
            }
        }

        return $evaluatedResult;
    }
}