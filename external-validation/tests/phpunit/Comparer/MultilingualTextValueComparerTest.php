<?php

namespace WikidataQuality\ExternalValidation\Test\Comparer;


use DataValues\MonolingualTextValue;
use DataValues\MultilingualTextValue;
use WikidataQuality\ExternalValidation\CrossCheck\DumpMetaInformation;
use WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\Comparer\MultilingualTextValueComparer
 *
 * @group WikidataQuality
 * @group WikidataQuality\ExternalValidation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class MultilingualTextValueComparerTest extends \PHPUnit_Framework_TestCase {
    private $testDumpMetaInformationEn;
    private $testDumpMetaInformationDe;
    private $testMonolingualTextValue;
    private $testMultilingualTextDataValue;


    protected function setUp() {
        parent::setUp();
        $this->testDumpMetaInformationEn = new DumpMetaInformation( 'json', 'en', 'Y-m-d', 'TestDB' );
        $this->testDumpMetaInformationDe = new DumpMetaInformation( 'json', 'de', 'Y-m-d', 'TestDB' );
        $this->testMonolingualTextValue = new MonolingualTextValue( 'en', 'foo' );
        $this->testMultilingualTextDataValue = new MultilingualTextValue( array( $this->testMonolingualTextValue ) );
    }

    protected function tearDown() {
        unset( $this->testDumpMetaInformationEn, $this->testDumpMetaInformationDe, $this->testMonolingualTextValue, $this->testMultilingualTextDataValue );
        parent::tearDown();
    }


    public function testConstructOne() {
        $externalValues = array( 'foo', 'bar' );
        $comparer = new MultilingualTextValueComparer( $this->testDumpMetaInformationEn, $this->testMultilingualTextDataValue, $externalValues );

        $this->assertEquals( $this->testDumpMetaInformationEn, $comparer->getDumpMetaInformation() );
        $this->assertEquals( $this->testMonolingualTextValue, $comparer->getDataValue() );
        $this->assertEquals( $externalValues, $comparer->getExternalValues() );
    }

    public function testConstructTwo() {
        $externalValues = array( 'foo', 'bar' );
        $comparer = new MultilingualTextValueComparer( $this->testDumpMetaInformationDe, $this->testMultilingualTextDataValue, $externalValues );

        $this->assertEquals( $this->testDumpMetaInformationDe, $comparer->getDumpMetaInformation() );
        $this->assertNull( $comparer->getDataValue() );
        $this->assertEquals( $externalValues, $comparer->getExternalValues() );
    }


    public function testExecuteOne() {
        $comparer = new MultilingualTextValueComparer( $this->testDumpMetaInformationEn, $this->testMultilingualTextDataValue, array( 'foo', 'bar' ) );
        $this->assertTrue( $comparer->execute() );

        $monolingualTextValue = $this->getTextInLanguage( $this->testMultilingualTextDataValue, $this->testDumpMetaInformationEn->getLanguage() );
        $this->assertEquals( $comparer->getLocalValues(), array( $monolingualTextValue->getText() ) );
    }

    public function testExecuteTwo() {
        $comparer = new MultilingualTextValueComparer( $this->testDumpMetaInformationEn, $this->testMultilingualTextDataValue, array( 'bar', 'foobar' ) );
        $this->assertFalse( $comparer->execute() );

        $monolingualTextValue = $this->getTextInLanguage( $this->testMultilingualTextDataValue, $this->testDumpMetaInformationEn->getLanguage() );
        $this->assertEquals( $comparer->getLocalValues(), array( $monolingualTextValue->getText() ) );
    }

    public function testExecuteThree() {
        $comparer = new MultilingualTextValueComparer( $this->testDumpMetaInformationEn, $this->testMultilingualTextDataValue, null );
        $this->assertFalse( $comparer->execute() );

        $monolingualTextValue = $this->getTextInLanguage( $this->testMultilingualTextDataValue, $this->testDumpMetaInformationEn->getLanguage() );
        $this->assertEquals( $comparer->getLocalValues(), array( $monolingualTextValue->getText() ) );
    }

    public function testExecuteFour() {
        $comparer = new MultilingualTextValueComparer( $this->testDumpMetaInformationDe, $this->testMultilingualTextDataValue, array( 'foo', 'bar' ) );
        $this->assertFalse( $comparer->execute() );

        $this->assertEquals( $comparer->getLocalValues(), array() );
    }


    /**
     * Extracts MonolingualTextValue in specified language of given MultilingualTextValue
     * @param MultilingualTextValue $multilingualTextValue
     * @param string $languageCode
     * @return MonolingualTextValue
     */
    private function getTextInLanguage( $multilingualTextValue, $languageCode ) {
        foreach( $multilingualTextValue->getTexts() as $text ) {
            if ( $text->getLanguageCode() == $languageCode ) {
                return $text;
            }
        }
    }
}