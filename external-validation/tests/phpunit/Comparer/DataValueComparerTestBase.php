<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


/**
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
abstract class DataValueComparerTestBase extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer::getExternalValueParser
     * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer::parseExternalValues
     * @dataProvider executeDataProvider
     */
    public function testExecute( $dumpMetaInformation, $localValue, $externalValues, $expectedResult, $expectedExternalValues )
    {
        $comparer = $this->createComparer( $dumpMetaInformation, $localValue, $externalValues );

        $this->assertEquals( $expectedResult, $comparer->execute() );
        $this->assertEquals( $expectedExternalValues, $comparer->getExternalValues() );
    }

    /*
     * Test cases for testExecute
     * @return array
     */
    public abstract function executeDataProvider();

    /*
     * Returns new instance of the comparer being tested with given arguments.
     * @return DataValueComparer
     */
    protected abstract function createComparer( $dumpMetaInformation, $localValue, $externalValues );
}
