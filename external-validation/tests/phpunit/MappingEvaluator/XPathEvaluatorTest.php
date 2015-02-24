<?php

namespace WikidataQuality\ExternalValidation\Test\MappingEvaluator;

use WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\MappingEvaluator;
use WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\XPathEvaluator;

/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\XPathEvaluator
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
        $this->testData = file_get_contents(__DIR__ . '/XMLData.xml');
        require __DIR__ . '/mapping.inc.php';
        $this->mapping = $mapping;
        $this->evaluator = new XPathEvaluator( $this->testData );
    }

    protected function tearDown() {
        unset( $this->evaluator, $this->mapping, $this->testData );
        parent::tearDown();
    }

    public function testEvaluate()
    {
        $nodeSelector = $this->mapping[ 'testcase one' ][ 'nodeSelector' ];
        $this->assertEquals( array('success'), $this->evaluator->evaluate( $nodeSelector ), 'should find path' );

        $nodeSelector = $this->mapping[ 'testcase two' ][ 'nodeSelector' ];
        $valueFormatter = $this->mapping[ 'testcase two' ][ 'valueFormatter' ];
        $this->assertEquals( array('success'), $this->evaluator->evaluate( $nodeSelector, $valueFormatter ), 'should format string' );

        $nodeSelector = $this->mapping[ 'testcase three' ][ 'nodeSelector' ];
        $this->assertEquals( array(), $this->evaluator->evaluate( $nodeSelector, $valueFormatter ), 'should find nothing' );
    }

    public function testGetEvaluator()
    {
        $evaluator = MappingEvaluator::getEvaluator( 'xml', $this->testData );
        $this->assertEquals( 'WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\XPathEvaluator', get_class( $evaluator ), 'should get XPathEvaluator' );
    }
}