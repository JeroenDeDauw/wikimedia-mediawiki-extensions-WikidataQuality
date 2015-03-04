<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\GlobeCoordinateValueComparer
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
class GlobeCoordinateValueComparerTest extends DataValueComparerTestBase
{
    /**
     * Test cases for testExecute
     * @return array
     */
    public function executeDataProvider()
    {
        return array(
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1 ),
                array( '64.000000 N, 26.000000 E' ),
                true,
                array(
                    new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1 )
                )
            ),
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1 ),
                array( '64 N, 26 E' ),
                true,
                array(
                    new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1 )
                )
            ),
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1 ),
                array( '42.000000 N, 32.000000 E' ),
                false,
                array(
                    new GlobeCoordinateValue( new LatLongValue( 42, 32 ), 1 )
                )
            ),
            array(
                new DumpMetaInformation( 'xml', 'en', 'd.m.Y', 'TestDB' ),
                new GlobeCoordinateValue( new LatLongValue( 64, 26 ), 1 ),
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