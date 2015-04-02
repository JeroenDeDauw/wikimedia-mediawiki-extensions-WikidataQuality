<?php

namespace WikidataQuality\ConstraintReport\Test\TypeChecker;

use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\TypeChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;

/**
 * @covers WikidataQuality\ConstraintReport\ConstraintCheck\Checker\TypeChecker
 *
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class TypeCheckerTest extends \MediaWikiTestCase {

    private $helper;
    private $lookup;
    private $value;
    private $propertyId;
    private $typeChecker;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->lookup = new JsonFileEntityLookup( __DIR__ );
        $this->value = new EntityIdValue( new ItemId( 'Q42' ) );
        $this->propertyId = new PropertyId( 'P1' );
        $this->typeChecker = new TypeChecker( $this->lookup, $this->helper );
    }

    protected function tearDown() {
        unset( $this->helper );
        unset( $this->lookup );
        unset( $this->value );
        unset( $this->propertyId );
        unset( $this->typeChecker );
        parent::tearDown();
    }


    /*
     * Following tests are testing the 'Inverse' constraint.
     */

    public function testCheckTypeConstraintInstanceValid() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q100', 'Q101' ), 'instance' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckTypeConstraintInstanceValidWithIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q2' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q100', 'Q101' ), 'instance' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckTypeConstraintInstanceValidWithMoreIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q3' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q100', 'Q101' ), 'instance' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckTypeConstraintSubclassValid() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q100', 'Q101' ), 'subclass' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckTypeConstraintSubclassValidWithIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q100', 'Q101' ), 'subclass' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckTypeConstraintSubclassValidWithMoreIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q6' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q100', 'Q101' ), 'subclass' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckTypeConstraintInstanceInvalid() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q200', 'Q201' ), 'instance' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintInstanceInvalidWithIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q2' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q200', 'Q201' ), 'instance' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintInstanceInvalidWithMoreIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q3' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q200', 'Q201' ), 'instance' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintSubclassInvalid() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q200', 'Q201' ), 'subclass' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintSubclassInvalidWithIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q200', 'Q201' ), 'subclass' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintSubclassInvalidWithMoreIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q6' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q200', 'Q201' ), 'subclass' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintMissingRelation() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( 'Q100', 'Q101' ), null );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintMissingClass() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->propertyId, $this->value, $entity->getStatements(), array( '' ), 'subclass' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    /*
     * Following tests are testing the 'Value type' constraint.
     */

    // todo: write tests for 'Value type' constraint

}