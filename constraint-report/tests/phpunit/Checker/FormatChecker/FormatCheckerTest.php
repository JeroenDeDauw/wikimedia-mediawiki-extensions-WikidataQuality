<?php

namespace WikidataQuality\ConstraintReport\Test\FormatChecker;

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

        $value1 = 'nm0001398';
        $value2 = 'tt1234567';
        $value3 = 'ch7654321';
        $value4 = 'ev7777777';
        $value5 = 'nm88888888';
        $value6 = 'nmabcdefg';
        $value7 = 'ab0001398';
        $value8 = '123456789';
        $value9 = 'nm000139';
        $value10 = 'nmnm0001398';

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

        $value1 = 'Populus × canescens';
        $value2 = 'Encephalartos friderici-guilielmi';
        $value3 = 'Eruca vesicaria subsp. sativa';
        $value4 = 'Euxoa (Chorizagrotis) lidia';
        $value5 = 'Hepatitis A';
        $value6 = 'Symphysodon (Cichlidae)';
        $value7 = 'eukaryota';
        $value8 = 'Plantago maritima agg.';
        $value9 = 'Deinococcus-Thermus';
        $value10 = 'Escherichia coli O157:H7';

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