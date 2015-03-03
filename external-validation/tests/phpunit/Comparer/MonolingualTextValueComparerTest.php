<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\MonolingualTextValue;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer
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
class MonolingualTextValueComparerTest extends DataValueComparerTestBase
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
                new MonolingualTextValue( 'en', 'foo' ),
                array( 'foo', 'bar' ),
                true,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MonolingualTextValue( 'en', 'foo' ),
                array( 'foobar', 'bar' ),
                false,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new MonolingualTextValue( 'en', 'foo' ),
                array( 'foo', 'bar' ),
                true,
                array( 'foo' )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new MonolingualTextValue( 'en', 'foo' ),
                null,
                false,
                array( 'foo' )
            )
        );
    }

    protected function createComparer( $dumpMetaInformation, $dataValue, $externalValues )
    {
        return new MonolingualTextValueComparer( $dumpMetaInformation, $dataValue, $externalValues );
    }
}