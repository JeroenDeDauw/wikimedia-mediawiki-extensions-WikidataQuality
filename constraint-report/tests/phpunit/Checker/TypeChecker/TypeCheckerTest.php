<?php

namespace WikidataQuality\ConstraintReport\Test\TypeChecker;

use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\TypeChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;

class TypeCheckerTest extends \PHPUnit_Framework_TestCase {

    private $helper;
    private $lookup;
    private $typeChecker;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->lookup = new JsonFileEntityLookup(  __DIR__ );
        $this->typeChecker = new TypeChecker( $this->lookup, $this->helper);
    }


    protected function tearDown()
    {
        unset($this->helper);
        unset($this->lookup);
        unset($this->typeChecker);
        parent::tearDown();
    }

    public function testCheckTypeConstraintInstanceValid()
    {
        // Q1 is TestItem with a statement date of birth: 1.1.1970 and instance of: person
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( 569, "irrelevant", $entity->getStatements(), array('Q215627', 'Q39201'), 'instance' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckTypeConstraintInstanceInvalid()
    {
        // Q2 is TestItem with a statement date of birth: 1.1.1970 and instance of: Berlin;
        $entity = $this->lookup->getEntity( new ItemId( 'Q2' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( 569, "irrelevant", $entity->getStatements(), array('Q215627', 'Q39201'), 'instance' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testCheckTypeConstraintSubclassValid()
    {
        // Q3 is TestItem with a statement date of birth: 1.1.1970 and subclass of: Q215627 ];
        $entity = $this->lookup->getEntity( new ItemId( 'Q3' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( 569, "irrelevant", $entity->getStatements(), array('Q215627', 'Q39201'), 'subclass' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckTypeConstraintSubclassValidWithIndirection()
    {
        // Q3 is TestItem with a statement date of birth: 1.1.1970 and subclass of: Q3 (test item from above) ];
        $entity = $this->lookup->getEntity( new ItemId( 'Q3' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( 569, "irrelevant", $entity->getStatements(), array('Q215627', 'Q39201'), 'subclass' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

}