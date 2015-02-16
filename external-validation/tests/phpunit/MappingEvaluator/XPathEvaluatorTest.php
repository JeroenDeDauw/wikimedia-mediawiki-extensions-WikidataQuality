<?php

namespace WikidataQuality\ExternalValidation\Test\MappingEvaluator;

//use WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\XPathEvaluator;
require_once 'C:\Program Files (x86)\Xampp\htdocs\mediawiki\extensions\Wikidata\extensions\WikidataQuality\external-validation\src\CrossCheck\MappingEvaluator\XPathEvaluator.php';
require_once 'C:\Program Files (x86)\Xampp\htdocs\mediawiki\extensions\Wikidata\extensions\WikidataQuality\external-validation\src\CrossCheck\MappingEvaluator\MappingEvaluator.php';

/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator
 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class XPathEvaluatorTest extends \PHPUnit_Framework_TestCase
{
    private $testData;
    private $mapping;
    private $evaluator;

    protected function setUp() {
        parent::setUp();
        $this->testData = __DIR__ . '/XMLData.xml';
        $this->mapping = __DIR__ . '/mapping.inc.php';
        $this->evaluator = new XPathEvaluator( $this->testData );
    }

    protected function tearDown() {
        unset($this->evaluator);
        unset($this->mapping);
        unset($this->testData);
        parent::tearDown();
    }

    public function testEvaluate()
    {
        $evaluator = $this->initializeXPathEvaluator();
        $mapping = $this->getMapping();

        $nodeSelector = $mapping[ "testcase one"[ "nodeSelector" ] ];
        $this->assertEquals( 'true', $evaluator->evaluate( $nodeSelector ) );

        $nodeSelector = $mapping[ "testcase two"[ "nodeSelector" ] ];
        $valueFormatter = $mapping[ "testcase two"[ "valueFormatter" ] ];
        $this->assertEquals( 'true', $evaluator->evaluate( $nodeSelector, $valueFormatter ) );

        $nodeSelector = $mapping[ "testcase tree"[ "nodeSelector" ] ];
        $this->assertEquals( null, $evaluator->evaluate( $nodeSelector, $valueFormatter ) ); // null funktioniert wahrscheinlich nicht, kann leider gerade nicht testen ...
    }

    public function testGetEvaluator()
    {
        $testData = $this->getTestData();
        $evaluator = MappingEvaluator::getEvaluator( 'xml', $testData );
        $this->assertEquals( 'XPathEvaluator', get_class( $evaluator ) );
    }
}