<?php

namespace WikidataQuality\ExternalValidation\Test\MappingEvaluator;


use WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\XPathEvaluator;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\XPathEvaluator
 *
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\MappingEvaluator
 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class XPathEvaluatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider constructDataProvider
     */
    public function testConstruct( $externalData, $expectedException )
    {
        $this->setExpectedException( $expectedException );

        $evaluator = new XPathEvaluator( $externalData );

        $this->assertEquals( $externalData, $evaluator->getExternalData() );
    }

    /**
     * Test cases for testConstruct
     * @return array
     */
    public function constructDataProvider()
    {
        return array(
            array(
                '<foobar />',
                null
            ),
            array(
                'foobar',
                'InvalidArgumentException'
            )
        );
    }


    /**
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate( $externalData, $nodeSelector, $valueFormatter, $expectedResult )
    {
        $evaluator = new XPathEvaluator( $externalData );
        $this->assertEquals( $expectedResult, $evaluator->evaluate( $nodeSelector, $valueFormatter ) );
    }

    /**
     * Test cases for testEvaluate
     * @return array
     */
    public function evaluateDataProvider()
    {
        $testData = file_get_contents( __DIR__ . '/testdata/data.xml' );

        return array(
            array(
                $testData,
                '/test/testcase[@case="one" and @result="true"]',
                null,
                array( 'success' )
            ),
            array(
                $testData,
                '/test/testcase[@case="two"]/result',
                'concat(substring-after(./text(), "."), substring-before(./text(), "."))',
                array( 'success' )
            ),
            array(
                $testData,
                '/test/testcase[@case="tree"]/result',
                null,
                array()
            )
        );
    }
}