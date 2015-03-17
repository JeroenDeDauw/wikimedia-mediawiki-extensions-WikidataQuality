<?php

namespace WikidataQuality\ExternalValidation\Tests\CrossCheck\Comparer;


use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer;
use WikidataQuality\ExternalValidation\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class GlobeCoordinateValueComparerTest extends DataValueComparerTestBase
{
    /**
     * Test cases for testExecute
     * @return array
     */
    public function executeDataProvider()
    {
        $dumpMetaInformation = $this->getDumpMetaInformationMock( 'en' );
        $localValue = new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1 );

        return array(
            array(
                $dumpMetaInformation,
                $localValue,
                array( '64.000000 N, 26.000000 E' ),
                true,
                array(
                    new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1 )
                )
            ),
            array(
                $dumpMetaInformation,
                $localValue,
                array( '64 N, 26 E' ),
                true,
                array(
                    new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1 )
                )
            ),
            array(
                $dumpMetaInformation,
                $localValue,
                array( '42.000000 N, 32.000000 E' ),
                false,
                array(
                    new GlobeCoordinateValue( new LatLongValue( 42, 32 ), 1 )
                )
            ),
            array(
                $dumpMetaInformation,
                $localValue,
                null,
                false,
                null
            )
        );
    }

    protected function createComparer( $dumpMetaInformation, $localValue, $externalValues )
    {
        return new GlobeCoordinateValueComparer( $dumpMetaInformation, $localValue, $externalValues );
    }
}