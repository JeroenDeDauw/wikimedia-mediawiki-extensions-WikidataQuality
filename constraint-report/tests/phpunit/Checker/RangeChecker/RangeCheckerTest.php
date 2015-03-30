<?php

namespace WikidataQuality\ConstraintReport\Test\RangeChecker;

use DataValues\DecimalValue;
use DataValues\QuantityValue;
use DataValues\TimeValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\RangeChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;


/**
 * @covers WikidataQuality\ConstraintReport\ConstraintCheck\Checker\RangeChecker
 *
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class RangeCheckerTest extends \MediaWikiTestCase {

    private $helper;
    private $lookup;
    private $timeValue;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->lookup = new JsonFileEntityLookup( __DIR__ );
        $this->timeValue = new TimeValue( '+00000001970-01-01T00:00:00Z', 0, 0, 0, 11, 'http://www.wikidata.org/entity/Q1985727');
    }

    protected function tearDown() {
        unset( $this->helper );
        unset( $this->lookup );
        unset( $this->timeValue );
        parent::tearDown();
    }

    public function testCheckRangeConstraintWithinRange() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );
        $value = new DecimalValue( 3.1415926536 );
        $checkResult = $rangeChecker->checkRangeConstraint( new PropertyId( 'P1457' ), new QuantityValue( $value, "1", $value, $value ), 0, 10, null, null );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckRangeConstraintTooSmall() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q2' ) );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );
        $value = new DecimalValue( 42 );
        $checkResult = $this->rangeChecker->checkRangeConstraint( new PropertyId( 'P1457' ), new QuantityValue( $value, "1", $value, $value ), 100, 1000, null, null );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckRangeConstraintTooBig() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q3' ) );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );
        $value = new DecimalValue( 3.141592 );
        $checkResult = $this->rangeChecker->checkRangeConstraint( new PropertyId( 'P1457' ), new QuantityValue( $value, "1", $value, $value ), 0, 1, null, null );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckDiffWithinRangeConstraintWithinRange() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkDiffWithinRangeConstraint( new PropertyId( 'P570' ), $this->timeValue, 'P569', 0, 150 );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckDiffWithinRangeConstraintTooSmall() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkDiffWithinRangeConstraint( new PropertyId( 'P570' ), $this->timeValue, 'P569', 50, 150 );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckDiffWithinRangeConstraintTooBig() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q6' ) );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkDiffWithinRangeConstraint( new PropertyId( 'P570' ), $this->timeValue, 'P569', 0, 150 );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

}