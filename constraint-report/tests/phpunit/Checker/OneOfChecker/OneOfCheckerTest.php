<?php

namespace WikidataQuality\ConstraintReport\Test\OneOfChecker;

use Wikibase\DataModel\Entity\PropertyId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\OneOfChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;


class OneOfCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $helper;
    private $oneOfChecker;

    protected function setUp()
    {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->oneOfChecker = new OneOfChecker( $this->helper );
    }

    protected function tearDown()
    {
        unset($this->helper);
        unset($this->oneOfChecker);
        parent::tearDown();
    }

    public function testCheckOneOfConstraint()
    {
        $valueIn = 'Q1';
        $valueNotIn = 'Q9';
        $values = '{{Q|1}}, {{Q|2}}, {{Q|3}}';
        $this->assertEquals('compliance', $this->oneOfChecker->checkOneOfConstraint(123, $valueIn, $values)->getStatus(), 'check should comply');
        $this->assertEquals('violation', $this->oneOfChecker->checkOneOfConstraint(123, $valueNotIn, $values)->getStatus(), 'check should not comply');
    }
}