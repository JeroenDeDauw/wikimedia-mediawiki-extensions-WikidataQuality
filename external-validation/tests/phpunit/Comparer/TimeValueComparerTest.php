<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\TimeValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\TimeValueComparer
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
class TimeValueComparerTest extends DataValueComparerTestBase
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
                new TimeValue( '+0000000000001955-03-11T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' ),
                array( '11.03.1955' ),
                true,
                array(
                    new TimeValue( '+0000000000001955-03-11T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new TimeValue( '+0000000000001955-03-11T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' ),
                array( '1955-03-11' ),
                true,
                array(
                    new TimeValue( '+0000000000001955-03-11T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new TimeValue( '+0000000000001955-03-11T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' ),
                array( '1991-05-23' ),
                false,
                array(
                    new TimeValue( '+0000000000001991-05-23T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' ),
                new TimeValue( '+0000000000002015-03-11T00:00:00Z', 0, 0, 0, 9, 'http://www.wikidata.org/entity/Q1985727' ),
                array( '2015' ),
                true,
                array(
                    new TimeValue( '+0000000000002015-00-00T00:00:00Z', 0, 0, 0, 9, 'http://www.wikidata.org/entity/Q1985727' )
                )
            ),
            array(
                new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' ),
                new TimeValue( '+0000000000001955-03-11T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727' ),
                null,
                false,
                null
            )
        );
    }

    protected function createComparer( $dumpMetaInformation, $localValue, $externalValues )
    {
        return new TimeValueComparer( $dumpMetaInformation, $localValue, $externalValues );
    }
}