<?php

namespace WikidataQuality\ConstraintReport\Test\OneOfChecker;

use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\OneOfChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;

/**
 * @covers WikidataQuality\ConstraintReport\ConstraintCheck\Checker\OneOfChecker
 *
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class OneOfCheckerTest extends \MediaWikiTestCase {

    private $helper;
    private $oneOfChecker;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->oneOfChecker = new OneOfChecker( $this->helper );
    }

    protected function tearDown() {
        unset( $this->helper );
        unset( $this->oneOfChecker );
        parent::tearDown();
    }

    public function testCheckOneOfConstraint() {
        $valueIn = new EntityIdValue( new ItemId( 'Q1' ) );
        $valueNotIn = new EntityIdValue( new ItemId( 'Q9' ) );
        $values = array( 'Q1', 'Q2', 'Q3' );
        $this->assertEquals( 'compliance', $this->oneOfChecker->checkOneOfConstraint( 123, $valueIn, $values )->getStatus(), 'check should comply' );
        $this->assertEquals( 'violation', $this->oneOfChecker->checkOneOfConstraint( 123, $valueNotIn, $values )->getStatus(), 'check should not comply' );
    }

}