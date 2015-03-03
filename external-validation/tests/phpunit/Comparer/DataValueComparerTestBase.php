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
     * @dataProvider executeDataProvider
     */
    public function testExecute( $dumpMetaInformation, $dataValue, $externalValues, $expectedResult, $expectedLocalValues )
    {
        $comparer = $this->createComparer( $dumpMetaInformation, $dataValue, $externalValues );

        $this->assertEquals( $expectedResult, $comparer->execute() );
        if ( is_array( $expectedLocalValues ) ) {
            $this->assertSame(
                array_diff( $expectedLocalValues, $comparer->getLocalValues() ),
                array_diff( $comparer->getLocalValues(), $expectedLocalValues )
            );
        } else {
            $this->assertEquals( $expectedLocalValues, $comparer->getLocalValues() );
        }
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
    protected abstract function createComparer( $dumpMetaInformation, $dataValue, $externalValues );
}
