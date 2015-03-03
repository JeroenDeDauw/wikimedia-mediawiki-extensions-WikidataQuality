<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\StringValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer
 *
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class StringValueComparerTest extends DataValueComparerTestBase
{
    /**
     * Test cases for testExecute
     * @return array
     */
    public function executeDataProvider()
    {
        return array(
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new StringValue( 'foo' ),
                array( 'foo', 'bar' ),
                true,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new StringValue( 'foo' ),
                array( 'foobar', 'bar' ),
                false,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new StringValue( 'foobar' ),
                array( 'foobar', 'bar' ),
                true,
                array( 'foobar' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new StringValue( 'foo' ),
                null,
                false,
                array( 'foo' )
            )
        );
    }

    protected function createComparer( $dumpMetaInformation, $dataValue, $externalValues )
    {
        return new StringValueComparer( $dumpMetaInformation, $dataValue, $externalValues );
    }
}