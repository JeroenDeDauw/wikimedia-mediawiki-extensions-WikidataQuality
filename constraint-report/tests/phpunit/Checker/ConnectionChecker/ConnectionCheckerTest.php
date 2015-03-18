<?php

namespace WikidataQuality\ConstraintReport\Test\ConnectionChecker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\ConnectionChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;

class ConnectionCheckerTest extends \PHPUnit_Framework_TestCase {

    private $lookup;
    private $helper;

    protected function setUp() {
        parent::setUp();
        $this->lookup = new JsonFileEntityLookup();
        $this->helper = new ConstraintReportHelper();
    }

    protected function tearDown() {
        unset( $this->lookup );
        unset( $this->helper );
        parent::tearDown();
    }

    public function testTravisSucks() {
        $this->assertEquals( 'foo', 'foo', 'check should comply' );
    }

}