<?php

namespace WikidataQuality\ConstraintReport\Test\ConnectionChecker;

use DataValues\StringValue;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
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
        $this->lookup = new JsonFileEntityLookup( __DIR__ );
        $this->helper = new ConstraintReportHelper();
    }

    protected function tearDown() {
        unset( $this->lookup );
        unset( $this->helper );
        parent::tearDown();
    }

    /**
     * Following tests are testing the symmetric constraint
     */
    public function testCheckSymmetricConstraintWithWrongSpouse() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkSymmetricConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q2' ) ), 'Q1' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckSymmetricConstraintWithCorrectSpouse() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkSymmetricConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q3' ) ), 'Q1' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckSymmetricConstraintWithWrongDataValue() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkSymmetricConstraint( new PropertyId( 'P188' ), new StringValue( 'Q3' ), 'Q1' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckSymmetricConstraintWithNonexistingEntity() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkSymmetricConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), 'Q1' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    /**
     * Following tests are testing the conflicts with constraint
     */
    public function testConflictsWithConstraintValid()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkConflictsWithConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), 'P2', array('') );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testConflictsWithConstraintProperty()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkConflictsWithConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), 'P2', array('') );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testConflictsWithConstraintPropertyButNotItem()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkConflictsWithConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), 'P2', array('Q1') );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testConflictsWithConstraintPropertyAndItem()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkConflictsWithConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), 'P2', array('Q42') );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testConflictsWithConstraintWithoutProperty()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkConflictsWithConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), null, array('') );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testConflictsWithConstraintPropertyAndNoValue()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q6' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkConflictsWithConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), 'P2', array('Q42') );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    /**
     * Following tests are testing the item constraint
     */
    public function testItemConstraintValid()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkItemConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), 'P2', array('') );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testItemConstraintProperty()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkItemConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), 'P2', array('') );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testItemConstraintPropertyButNotItem()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkItemConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), 'P2', array('Q1') );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testItemConstraintPropertyAndItem()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkItemConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), 'P2', array('Q42') );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testItemConstraintWithoutProperty()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $connectionChecker = new ConnectionChecker( $entity->getStatements(), $this->lookup, $this->helper );
        $checkResult = $connectionChecker->checkItemConstraint( new PropertyId( 'P188' ), new EntityIdValue( new ItemId( 'Q100' ) ), null, array('') );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

}