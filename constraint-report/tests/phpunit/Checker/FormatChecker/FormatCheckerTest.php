<?php

namespace WikidataQuality\ConstraintReport\Test\FormatChecker;

use DataValues\StringValue;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\FormatChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;

class FormatCheckerTest extends \PHPUnit_Framework_TestCase {
    
    private $helper;
    private $formatChecker;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->formatChecker = new FormatChecker( $this->helper );
    }

    protected function tearDown() {
        unset( $this->helper );
        unset( $this->formatChecker );
        parent::tearDown();
    }

    public function testFormatConstraintImdb() {
        $pattern = '(tt|nm|ch|co|ev)\d{7}';

        $value1 = new StringValue( 'nm0001398' );
        $value2 = new StringValue( 'tt1234567' );
        $value3 = new StringValue( 'ch7654321' );
        $value4 = new StringValue( 'ev7777777' );
        $value5 = new StringValue( 'nm88888888' );
        $value6 = new StringValue( 'nmabcdefg' );
        $value7 = new StringValue( 'ab0001398' );
        $value8 = new StringValue( '123456789' );
        $value9 = new StringValue( 'nm000139' );
        $value10 = new StringValue( 'nmnm0001398' );

        $this->assertEquals( 'compliance', $this->formatChecker->checkFormatConstraint( 345, $value1, $pattern )->getStatus(), 'check should comply' );
        $this->assertEquals( 'compliance', $this->formatChecker->checkFormatConstraint( 345, $value2, $pattern )->getStatus(), 'check should comply' );
        $this->assertEquals( 'compliance', $this->formatChecker->checkFormatConstraint( 345, $value3, $pattern )->getStatus(), 'check should comply' );
        $this->assertEquals( 'compliance', $this->formatChecker->checkFormatConstraint( 345, $value4, $pattern )->getStatus(), 'check should comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value5, $pattern )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value6, $pattern )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value7, $pattern )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value8, $pattern )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value9, $pattern )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value10, $pattern )->getStatus(), 'check should not comply' );
    }

    public function testFormatConstraintTaxonName() {
        $pattern = "(|somevalue|novalue|.*virus.*|.*viroid.*|.*phage.*|((×)?[A-Z]([a-z]+-)?[a-z]+(( [A-Z]?[a-z]+)|( ([a-z]+-)?([a-z]+-)?[a-z]+)|( ×([a-z]+-)?([a-z]+-)?([a-z]+-)?([a-z]+-)?[a-z]+)|( \([A-Z][a-z]+\) [a-z]+)|( (‘|')[A-Z][a-z]+(('|’)s)?( de)?( [A-Z][a-z]+(-([A-Z])?[a-z]+)*)*('|’)*)|( ×| Group| (sub)?sp\.| (con)?(sub)?(notho)?var\.| (sub)?ser\.| (sub)?sect\.| subg\.| (sub)?f\.))*))";

        $value1 = new StringValue( 'Populus × canescens' );
        $value2 = new StringValue( 'Encephalartos friderici-guilielmi' );
        $value3 = new StringValue( 'Eruca vesicaria subsp. sativa' );
        $value4 = new StringValue( 'Euxoa (Chorizagrotis) lidia' );
        $value5 = new StringValue( 'Hepatitis A' );
        $value6 = new StringValue( 'Symphysodon (Cichlidae)' );
        $value7 = new StringValue( 'eukaryota' );
        $value8 = new StringValue( 'Plantago maritima agg.' );
        $value9 = new StringValue( 'Deinococcus-Thermus' );
        $value10 = new StringValue( 'Escherichia coli O157:H7' );

        $this->assertEquals( 'compliance', $this->formatChecker->checkFormatConstraint( 345, $value1, $pattern )->getStatus(), 'check should comply' );
        $this->assertEquals( 'compliance', $this->formatChecker->checkFormatConstraint( 345, $value2, $pattern )->getStatus(), 'check should comply' );
        $this->assertEquals( 'compliance', $this->formatChecker->checkFormatConstraint( 345, $value3, $pattern )->getStatus(), 'check should comply' );
        $this->assertEquals( 'compliance', $this->formatChecker->checkFormatConstraint( 345, $value4, $pattern )->getStatus(), 'check should comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value5, $pattern )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value6, $pattern )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value7, $pattern )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value8, $pattern )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value9, $pattern )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->formatChecker->checkFormatConstraint( 345, $value10, $pattern )->getStatus(), 'check should not comply' );
    }

}