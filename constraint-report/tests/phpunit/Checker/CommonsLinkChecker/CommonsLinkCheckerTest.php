<?php

namespace WikidataQuality\ConstraintReport\Test\CommonsLinkChecker;

use DataValues\StringValue;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\CommonsLinkChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;

/**
 * @covers WikidataQuality\ConstraintReport\ConstraintCheck\Checker\CommonsLinkChecker
 *
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CommonsLinkCheckerTest extends \MediaWikiTestCase {

    private $helper;
    private $commonsLinkChecker;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->commonsLinkChecker = new CommonsLinkChecker( $this->helper );
    }

    protected function tearDown() {
        unset( $this->helper );
        unset( $this->commonsLinkChecker );
        parent::tearDown();
    }

    public function testCheckCommonsLinkConstraintValid() {
        $value = new StringValue( 'President Barack Obama.jpg' );
        $this->assertEquals( 'compliance', $this->commonsLinkChecker->checkCommonsLinkConstraint( new PropertyID( 'P1' ), $value, 'File' )->getStatus(), 'check should comply' );
    }

    public function testCheckCommonsLinkConstraintInvalid() {
        $value1 = new StringValue( 'President_Barack_Obama.jpg' );
        $value2 = new StringValue( 'President%20Barack%20Obama.jpg' );
        $value3 = new StringValue( 'File:President Barack Obama.jpg' );
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( new PropertyID( 'P1' ), $value1, 'File' )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( new PropertyID( 'P1' ), $value2, 'File' )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( new PropertyID( 'P1' ), $value3, 'File' )->getStatus(), 'check should not comply' );
    }

    public function testCheckCommonsLinkConstraintWithoutNamespace() {
        $value = new StringValue( 'President Barack Obama.jpg' );
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( new PropertyID( 'P1' ), $value, null )->getStatus(), 'check should not comply' );
    }

    public function testCheckCommonsLinkConstraintNotExistent() {
        $value = new StringValue( 'Qwertz Asdfg Yxcv.jpg' );
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( new PropertyID( 'P1' ), $value, 'File' )->getStatus(), 'check should not comply' );
    }

    public function testCheckCommonsLinkConstraintNoStringValue() {
        $value = new EntityIdValue( new ItemId( 'Q1' ) );
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( new PropertyID( 'P1' ), $value, 'File' )->getStatus(), 'check should not comply' );
    }

}