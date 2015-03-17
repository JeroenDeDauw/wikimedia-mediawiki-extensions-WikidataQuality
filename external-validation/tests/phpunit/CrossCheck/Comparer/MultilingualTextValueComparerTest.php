<?php

namespace WikidataQuality\ExternalValidation\Tests\CrossCheck\Comparer;


use DataValues\MonolingualTextValue;
use DataValues\MultilingualTextValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer;
use WikidataQuality\ExternalValidation\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\MonolingualTextValueComparer
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class MultilingualTextValueComparerTest extends DataValueComparerTestBase
{
    /**
     * @dataProvider constructDataProvider
     */
    public function testConstruct( $dumpMetaInformation, $localValue, $externalValues )
    {
        $comparer = $this->createComparer( $dumpMetaInformation, $localValue, $externalValues );

        $this->assertEquals( $dumpMetaInformation, $comparer->getDumpMetaInformation() );
        $this->assertEquals( $localValue, $comparer->getLocalValue() );
        $this->assertEquals( $externalValues, $comparer->getExternalValues() );
    }

    public function constructDataProvider()
    {
        $monolingualTextValue = new MonolingualTextValue( 'en', 'foo' );

        return array(
            array(
                $this->getDumpMetaInformationMock( 'en' ),
                new MultilingualTextValue( array( $monolingualTextValue ) ),
                array( 'foo', 'bar' )
            ),
            array(
                $this->getDumpMetaInformationMock( 'de' ),
                new MultilingualTextValue( array( $monolingualTextValue ) ),
                array( 'foo', 'bar' )
            )
        );
    }


    /**
     * Test cases for testExecute
     * @return array
     */
    public function executeDataProvider()
    {
        $dumpMetaInformationEn = $this->getDumpMetaInformationMock( 'en' );
        $dumpMetaInformationDe = $this->getDumpMetaInformationMock( 'de' );
        $localValueEn = new MultilingualTextValue( array( new MonolingualTextValue( 'en', 'foo' ) ) );
        $localValueDe = new MultilingualTextValue( array( new MonolingualTextValue( 'de', 'foo' ) ) );

        return array(
            array(
                $dumpMetaInformationEn,
                $localValueEn,
                array( 'foo', 'bar' ),
                true,
                array(
                    new MonolingualTextValue( 'en', 'foo' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                $dumpMetaInformationEn,
                $localValueEn,
                array( 'foobar', 'bar' ),
                false,
                array(
                    new MonolingualTextValue( 'en', 'foobar' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                $dumpMetaInformationDe,
                $localValueEn,
                array( 'foo', 'bar' ),
                false,
                array(
                    new MonolingualTextValue( 'de', 'foo' ),
                    new MonolingualTextValue( 'de', 'bar' )
                )
            ),
            array(
                $dumpMetaInformationEn,
                $localValueDe,
                array( 'foo', 'bar' ),
                false,
                array(
                    new MonolingualTextValue( 'en', 'foo' ),
                    new MonolingualTextValue( 'en', 'bar' )
                )
            ),
            array(
                $dumpMetaInformationEn,
                $localValueEn,
                null,
                false,
                null
            )
        );
    }

    protected function createComparer( $dumpMetaInformation, $localValue, $externalValues )
    {
        return new MultilingualTextValueComparer( $dumpMetaInformation, $localValue, $externalValues );
    }
}