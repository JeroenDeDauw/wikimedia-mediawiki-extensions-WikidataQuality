<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\MonolingualTextValue;
use DataValues\StringValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer;
use WikidataQuality\ExternalValidation\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
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
        $dumpMetaInformationEn = $this->getDumpMetaInformationMock( 'en' );
        $dumpMetaInformationDe = $this->getDumpMetaInformationMock( 'de' );

        return array(
            array(
                $dumpMetaInformationEn,
                new StringValue( 'foo' ),
                array( 'foo', 'bar' ),
                true,
                array(
                    new MonolingualTextValue( 'en', 'foo' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                $dumpMetaInformationEn,
                new StringValue( 'foo' ),
                array( 'foobar', 'bar' ),
                false,
                array(
                    new MonolingualTextValue( 'en', 'foobar' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                $dumpMetaInformationDe,
                new StringValue( 'foobar' ),
                array( 'foobar', 'bar' ),
                true,
                array(
                    new MonolingualTextValue( 'de', 'foobar' ),
                    new MonolingualTextValue( 'de', 'bar' )
                )
            ),
            array(
                $dumpMetaInformationEn,
                new StringValue( 'foo' ),
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