<?php

namespace WikidataQuality\ConstraintReport\Test\TypeChecker;

use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\TypeChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;

class TypeCheckerTest extends \PHPUnit_Framework_TestCase {

    private $helper;
    private $lookup;

    protected function setUp()
    {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->lookup = new JsonFileEntityLookup(  __DIR__ );
    }

    protected function tearDown()
    {
        unset($this->helper);
        parent::tearDown();
    }


    public function testCheckTypeConstraintValid()
    {
        // Q1 ist TestItem with a statement date of birth: 1.1.1970 and instance of: person' ];
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $typeChecker = new TypeChecker( $this->lookup, $this->helper);
        $checkResult = $typeChecker->checkTypeConstraint( 569, "irrelevant", $entity->getStatements(), array('Q215627', 'Q39201'), 'instance' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

}