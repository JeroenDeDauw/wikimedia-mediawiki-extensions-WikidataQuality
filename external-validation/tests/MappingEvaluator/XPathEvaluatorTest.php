<?php

namespace WikidataQuality\ExternalValidation\Test\MappingEvaluator;

use WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\XPathEvaluator;

/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator
 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */

class XPathEvaluatorTest extends \PHPUnit_Framework_TestCase {

    protected function getTestData() {
        return __DIR__ . '/XMLData.xml';
    }

    protected function getMapping() {
        return __DIR__ . '/mapping.inc.php';
    }

    protected function initializeXPathEvaluator() {
        $testData = $this->getTestData();
        return new XPathEvaluator($testData);
    }

    public function testEvaluate() {
        $evaluator = $this->initializeXPathEvaluator();
        $mapping = $this->getMapping();

        $nodeSelector = $mapping["testcase one"["nodeSelector"]];
        $this->assertEquals('true', $evaluator->evaluate($nodeSelector));

        $nodeSelector = $mapping["testcase two"["nodeSelector"]];
        $valueFormatter = $mapping["testcase two"["valueFormatter"]];
        $this->assertEquals('true', $evaluator->evaluate($nodeSelector, $valueFormatter));

        $nodeSelector = $mapping["testcase tree"["nodeSelector"]];
        $this->assertEquals(null, $evaluator->evaluate($nodeSelector, $valueFormatter)); // null funktioniert wahrscheinlich nicht, kann leider gerade nicht testen ...
    }

    public function testGetEvaluator() {
        $testData = $this->getTestData();
        $evaluator = MappingEvaluator::getEvaluator( 'xml', $testData );
        $this->assertEquals('XPathEvaluator', get_class($evaluator));
    }
}