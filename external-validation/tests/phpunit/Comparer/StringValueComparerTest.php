<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\MonolingualTextValue;
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
                array(
                    new MonolingualTextValue( 'en', 'foo' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new StringValue( 'foo' ),
                array( 'foobar', 'bar' ),
                false,
                array(
                    new MonolingualTextValue( 'en', 'foobar' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new StringValue( 'foobar' ),
                array( 'foobar', 'bar' ),
                true,
                array(
                    new MonolingualTextValue( 'de', 'foobar' ),
                    new MonolingualTextValue( 'de', 'bar' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MonolingualTextValue( 'en', 'foo' ),
                null,
                false,
                null
            )
        );
    }

    protected function createComparer( $dumpMetaInformation, $localValue, $externalValues )
    {
        return new StringValueComparer( $dumpMetaInformation, $localValue, $externalValues );
    }
}