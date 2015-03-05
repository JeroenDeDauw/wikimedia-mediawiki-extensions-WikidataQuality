<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\QuantityValue;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer;
use WikidataQuality\ExternalValidation\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\QuantityValueComparer
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class QuantityValueComparerTest extends DataValueComparerTestBase
{
    /**
     * Test cases for testExecute
     * @return array
     */
    public function executeDataProvider()
    {
        $dumpMetaInformation = $this->getDumpMetaInformationMock( 'en' );

        return array(
            array(
                $dumpMetaInformation,
                QuantityValue::newFromNumber( 42, '1', 44, 40 ),
                array( '42' ),
                true,
                array(
                    QuantityValue::newFromNumber( 42, '1', 43, 41 )
                )
            ),
            array(
                $dumpMetaInformation,
                QuantityValue::newFromNumber( 42, '1', 44, 40 ),
                array( '41' ),
                true,
                array(
                    QuantityValue::newFromNumber( 41, '1', 42, 40 )
                )
            ),
            array(
                $dumpMetaInformation,
                QuantityValue::newFromNumber( 42, '1' ),
                array( '23' ),
                false,
                array(
                    QuantityValue::newFromNumber( 23, '1', 24, 22 )
                )
            ),
            array(
                $dumpMetaInformation,
                QuantityValue::newFromNumber( 42, '1' ),
                array( '42' ),
                true,
                array(
                    QuantityValue::newFromNumber( 42, '1', 43, 41 )
                )
            ),
            array(
                $dumpMetaInformation,
                QuantityValue::newFromNumber( 42, '1' ),
                array( '44' ),
                false,
                array(
                    QuantityValue::newFromNumber( 44, '1', 45, 43 )
                )
            ),
            array(
                $dumpMetaInformation,
                QuantityValue::newFromNumber( 42, '1', 44, 40 ),
                null,
                false,
                null
            )
        );
    }

    protected function createComparer( $dumpMetaInformation, $localValue, $externalValues )
    {
        return new QuantityValueComparer( $dumpMetaInformation, $localValue, $externalValues );
    }
}