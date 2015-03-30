<?php

namespace WikidataQuality\ConstraintReport\Test\CommonsLinkChecker;

use DataValues\StringValue;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\CommonsLinkChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;

class CommonsLinkCheckerTest extends \PHPUnit_Framework_TestCase {

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
        $this->assertEquals('compliance', $this->commonsLinkChecker->checkCommonsLinkConstraint( 1, $value, 'File' )->getStatus(), 'check should comply');
    }

    public function testCheckCommonsLinkConstraintNotValid() {
        $value1 = new StringValue( 'President_Barack_Obama.jpg' );
        $value2 = new StringValue( 'President%20Barack%20Obama.jpg' );
        $value3 = new StringValue( 'File:President Barack Obama.jpg' );
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( 1, $value1, 'File' )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( 1, $value2, 'File' )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( 1, $value3, 'File' )->getStatus(), 'check should not comply' );
    }

    public function testCheckCommonsLinkConstraintNotExistent() {
        $value = new StringValue( 'Qwertz Asdfg Yxcv.jpg' );
        $this->assertEquals('violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( 1, $value, 'File' )->getStatus(), 'check should not comply' );
    }

}