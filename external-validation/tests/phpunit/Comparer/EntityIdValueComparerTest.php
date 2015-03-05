<?php

namespace WikidataQuality\ExternalValidation\Tests\Comparer;


use DataValues\MonolingualTextValue;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\EntityIdValueComparer
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class EntityIdValueComparerTest extends DataValueComparerTestBase
{
    /**
     * Test cases for testExecute
     * @return array
     */
    public function executeDataProvider()
    {
        $dumpMetaInformationEn = $this->getDumpMetaInformationMock( 'en' );
        $dumpMetaInformationDe = $this->getDumpMetaInformationMock( 'de' );
        $dumpMetaInformationEs = $this->getDumpMetaInformationMock( 'es' );
        $localValueQ1 = new EntityIdValue( new ItemId( 'Q1' ) );
        $localValueQ2 = new EntityIdValue( new ItemId( 'Q2' ) );

        return array(
            array(
                $dumpMetaInformationEn,
                $localValueQ1,
                array( 'foo' ),
                true,
                array (
                    new MonolingualTextValue( 'en', 'foo' )
                )
            ),
            array(
                $dumpMetaInformationEn,
                $localValueQ1,
                array( 'baz' ),
                false,
                array(
                    new MonolingualTextValue( 'en', 'baz' )
                )
            ),
            array(
                $dumpMetaInformationDe,
                $localValueQ1,
                array( 'Fubar' ),
                true,
                array(
                    new MonolingualTextValue( 'de', 'Fubar' )
                )
            ),
            array(
                $dumpMetaInformationEs,
                $localValueQ1,
                array( 'foo' ),
                false,
                array(
                    new MonolingualTextValue( 'es', 'foo' )
                )
            ),
            array(
                $dumpMetaInformationEn,
                $localValueQ1,
                null,
                false,
                null
            ),
            array(
                $dumpMetaInformationEn,
                $localValueQ2,
                array( 'foo' ),
                false,
                array(
                    new MonolingualTextValue( 'en', 'foo' )
                )
            )
        );
    }


    protected function createComparer( $dumpMetaInformation, $localValue, $externalValues )
    {
        $mock = $this->getMockBuilder( 'WikidataQuality\ExternalValidation\CrossCheck\Comparer\EntityIdValueComparer' )
            ->setMethods( array( 'getEntityLookup' ) )
            ->setConstructorArgs( array( $dumpMetaInformation, $localValue, $externalValues ) )
            ->getMock();
        $mock->method( 'getEntityLookup' )
            ->willReturn( new JsonFileEntityLookup( __DIR__ . '/testdata' ) );

        return $mock;
    }
}