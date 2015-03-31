<?php

namespace WikidataQuality\ConstraintReport\Test\Helper;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;

/**
 * @covers WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class ConstraintReportHelperTest extends \MediaWikiTestCase {

    private $helper;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
    }

    protected function tearDown() {
        parent::tearDown();
        unset( $this->helper );
    }

    public function testRemoveBrackets()
    {
        $templateString = '{{Q|1234}}, {{Q|42}}';
        $expected = 'Q1234, Q42';
        $this->assertEquals( $expected, $this->helper->removeBrackets( $templateString ) );
    }

    public function testStringToArray()
    {
        $templateString = '{{Q|1234}}, {{Q|42}}';
        $expected = array( 'Q1234', 'Q42' );
        $this->assertEquals( $expected, $this->helper->stringToArray( $templateString ) );
    }

    public function testEmptyStringToArray()
    {
        $templateString = '';
        $expected = array( '' );
        $this->assertEquals( $expected, $this->helper->stringToArray( $templateString ) );
    }
}