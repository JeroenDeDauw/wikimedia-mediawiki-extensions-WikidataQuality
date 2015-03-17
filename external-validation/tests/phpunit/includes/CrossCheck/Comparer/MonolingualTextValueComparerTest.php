<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\MonolingualTextValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer;
use WikidataQuality\ExternalValidation\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
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
        $dumpMetaInformationEn = $this->getDumpMetaInformationMock( 'en' );
        $dumpMetaInformationDe = $this->getDumpMetaInformationMock( 'de' );
        $localValue = new MonolingualTextValue( 'en', 'foo' );

        return array(
            array(
                $dumpMetaInformationEn,
                $localValue,
                array( 'foo', 'bar' ),
                true,
                array(
                    new MonolingualTextValue( 'en', 'foo' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                $dumpMetaInformationEn,
                $localValue,
                array( 'foobar', 'bar' ),
                false,
                array(
                    new MonolingualTextValue( 'en', 'foobar' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                $dumpMetaInformationDe,
                $localValue,
                array( 'foo', 'bar' ),
                true,
                array(
                    new MonolingualTextValue( 'de', 'foo' ),
                    new MonolingualTextValue( 'de', 'bar' )
                )
            ),
            array(
                $dumpMetaInformationEn,
                $localValue,
                null,
                false,
                null
            )
        );
    }

    protected function createComparer( $dumpMetaInformation, $localValue, $externalValues )
    {
        return new MonolingualTextValueComparer( $dumpMetaInformation, $localValue, $externalValues );
    }
}