<?php

namespace WikidataQuality\ConstraintReport\Test\QualifierChecker;

use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\QualifierChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;

class QualifierCheckerTest extends \PHPUnit_Framework_TestCase {

    private $helper;
    private $qualifiersList;
    private $lookup;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->qualifiersList = array( 'P580', 'P582', 'P1365', 'P1366', 'P642', 'P805' );
        $this->lookup = new JsonFileEntityLookup(  __DIR__ );
    }

    protected function tearDown() {
        unset( $this->helper );
        unset( $this->qualifiersList );
        parent::tearDown();
    }

    private function getFirstStatement( $entity ) {
        foreach( $entity->getStatements() as $statement ) {
            return $statement;
        }
    }

    public function testQualifierConstraintQualifierProperty() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $qualifierChecker = new QualifierChecker( $entity->getStatements(), $this->helper );

        $checkResult = $qualifierChecker->checkQualifierConstraint( 'P580', 'Q1384' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testQualifiersConstraint() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q2' ) );
        $qualifierChecker = new QualifierChecker( $entity->getStatements(), $this->helper );

        $checkResult = $qualifierChecker->checkQualifiersConstraint( 'P39', 'Q11696', $this->getFirstStatement( $entity ),  $this->qualifiersList );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testQualifiersConstraintToManyQualifiers() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q3' ) );
        $qualifierChecker = new QualifierChecker( $entity->getStatements(), $this->helper );

        $checkResult = $qualifierChecker->checkQualifiersConstraint( 'P39', 'Q11696', $this->getFirstStatement( $entity ),  $this->qualifiersList );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

}