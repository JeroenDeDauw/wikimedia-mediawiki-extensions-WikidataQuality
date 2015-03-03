<?php

namespace WikidataQuality\ExternalValidation\Test\MappingEvaluator;

use WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\MappingEvaluator;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\MappingEvaluator
 *
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\XPathEvaluator
 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class MappingEvaluatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider constructDataProvider
     */
    public function testConstruct( $externalData )
    {
        $evaluatorMock = $this->getMappingEvaluatorMock( $externalData );

        $this->assertEquals( $externalData, $evaluatorMock->getExternalData() );
    }

    /**
     * Test cases for testConstruct
     * @return array
     */
    public function constructDataProvider()
    {
        return array(
            array(
                'foobar'
            )
        );
    }


    /**
     * @dataProvider getEvaluatorDataProvider
     */
    public function testGetEvaluator( $dataFormat, $externalData, $expectedEvaluatorClass )
    {
        $evaluator = MappingEvaluator::getEvaluator( $dataFormat, $externalData );

        if ( $expectedEvaluatorClass ) {
            $this->assertInstanceOf( $expectedEvaluatorClass, $evaluator );
        } else {
            $this->assertNull( $evaluator );
        }
    }

    /**
     * Test cases for testGetEvaluator
     * @return array
     */
    public function getEvaluatorDataProvider()
    {
        return array(
            array(
                'xml',
                '<foobar />',
                'WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\XPathEvaluator'
            ),
            array(
                'txt',
                'foobar',
                null
            )
        );
    }


    /**
     * Returns MappingEvaluator mock with given arguments
     * @param string $externalData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMappingEvaluatorMock( $externalData )
    {
        return $this->getMockForAbstractClass(
            'WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\MappingEvaluator',
            array( $externalData )
        );
    }
}