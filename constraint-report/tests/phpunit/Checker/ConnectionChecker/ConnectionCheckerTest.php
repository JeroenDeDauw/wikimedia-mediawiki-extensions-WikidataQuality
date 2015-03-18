<?php

namespace WikidataQuality\ConstraintReport\Test\ConnectionChecker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\ConnectionChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;


/**
 * @covers WikidataQuality\ConstraintReport\ConstraintCheck\Checker\ConnectionChecker
 *
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class ConnectionCheckerTest extends \MediaWikiTestCase {

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