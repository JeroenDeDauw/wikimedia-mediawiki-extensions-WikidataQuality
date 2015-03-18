<?php

namespace WikidataQuality\ConstraintReport\Test\CommonsLinkChecker;

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
        $value = 'President Barack Obama.jpg';
        $this->assertEquals('compliance', $this->commonsLinkChecker->checkCommonsLinkConstraint( $value )->getStatus(), 'check should comply');
    }

    public function testCheckCommonsLinkConstraintNotValid() {
        $value1 = 'President_Barack_Obama.jpg';
        $value2 = 'President%20Barack%20Obama.jpg';
        $value3 = 'File:President Barack Obama.jpg';
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( $value1 )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( $value2 )->getStatus(), 'check should not comply' );
        $this->assertEquals( 'violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( $value3 )->getStatus(), 'check should not comply' );
    }

    public function testCheckCommonsLinkConstraintNotExistent() {
        $value = 'Qwertz Asdfg Yxcv.jpg';
        $this->assertEquals('violation', $this->commonsLinkChecker->checkCommonsLinkConstraint( $value )->getStatus(), 'check should not comply' );
    }

}