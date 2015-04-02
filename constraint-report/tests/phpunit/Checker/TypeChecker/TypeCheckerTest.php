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

    private $typePropertyId;
    private $typeValue;
    
    private $valueTypePropertyId;
    
    private $typeChecker;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->lookup = new JsonFileEntityLookup( __DIR__ );
        
        $this->typePropertyId = new PropertyId( 'P1' );
        $this->typeValue = new EntityIdValue( new ItemId( 'Q42' ) );

        $this->valueTypePropertyId = new PropertyId( 'P1234' );
        
        $this->typeChecker = new TypeChecker( $this->lookup, $this->helper );
    }

    protected function tearDown() {
        unset( $this->helper );
        unset( $this->lookup );

        unset( $this->typePropertyId );
        unset( $this->value );

        unset( $this->valueTypePropertyId );

        unset( $this->typeChecker );
        parent::tearDown();
    }
    
    /*
     * Following tests are testing the 'Inverse' constraint.
     */

    // relation instance

    public function testCheckTypeConstraintInstanceValid() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q100', 'Q101' ), 'instance' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckTypeConstraintInstanceValidWithIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q2' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q100', 'Q101' ), 'instance' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckTypeConstraintInstanceValidWithMoreIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q3' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q100', 'Q101' ), 'instance' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    // relation subclass

    public function testCheckTypeConstraintSubclassValid() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q100', 'Q101' ), 'subclass' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckTypeConstraintSubclassValidWithIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q100', 'Q101' ), 'subclass' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckTypeConstraintSubclassValidWithMoreIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q6' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q100', 'Q101' ), 'subclass' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    // relation instance, violations

    public function testCheckTypeConstraintInstanceInvalid() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q200', 'Q201' ), 'instance' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintInstanceInvalidWithIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q2' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q200', 'Q201' ), 'instance' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintInstanceInvalidWithMoreIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q3' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q200', 'Q201' ), 'instance' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    // relation subclass, violations

    public function testCheckTypeConstraintSubclassInvalid() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q200', 'Q201' ), 'subclass' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintSubclassInvalidWithIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q200', 'Q201' ), 'subclass' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintSubclassInvalidWithMoreIndirection() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q6' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q200', 'Q201' ), 'subclass' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    // edge cases

    public function testCheckTypeConstraintMissingRelation() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( 'Q100', 'Q101' ), null );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckTypeConstraintMissingClass() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $checkResult = $this->typeChecker->checkTypeConstraint( $this->typePropertyId, $this->typeValue, $entity->getStatements(), array( '' ), 'subclass' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    /*
     * Following tests are testing the 'Value type' constraint.
     */

    // relation instance

    public function testCheckValueTypeConstraintInstanceValid() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q1' ) ), array( 'Q100', 'Q101' ), 'instance' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckValueTypeConstraintInstanceValidWithIndirection() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q2' ) ), array( 'Q100', 'Q101' ), 'instance' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckValueTypeConstraintInstanceValidWithMoreIndirection() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q3' ) ), array( 'Q100', 'Q101' ), 'instance' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    // relation subclass

    public function testCheckValueTypeConstraintSubclassValid() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q4' ) ), array( 'Q100', 'Q101' ), 'subclass' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckValueTypeConstraintSubclassValidWithIndirection() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q5' ) ), array( 'Q100', 'Q101' ), 'subclass' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    public function testCheckValueTypeConstraintSubclassValidWithMoreIndirection() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q6' ) ), array( 'Q100', 'Q101' ), 'subclass' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), 'check should comply' );
    }

    // relation instance, violations

    public function testCheckValueTypeConstraintInstanceInvalid() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q1' ) ), array( 'Q200', 'Q201' ), 'instance' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckValueTypeConstraintInstanceInvalidWithIndirection() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q2' ) ), array( 'Q200', 'Q201' ), 'instance' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckValueTypeConstraintInstanceInvalidWithMoreIndirection() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q3' ) ), array( 'Q200', 'Q201' ), 'instance' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    // relation subclass, violations

    public function testCheckValueTypeConstraintSubclassInvalid() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q4' ) ), array( 'Q200', 'Q201' ), 'subclass' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckValueTypeConstraintSubclassInvalidWithIndirection() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q5' ) ), array( 'Q200', 'Q201' ), 'subclass' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    public function testCheckValueTypeConstraintSubclassInvalidWithMoreIndirection() {
        $checkResult = $this->typeChecker->checkValueTypeConstraint( $this->valueTypePropertyId, new EntityIdValue( new ItemId( 'Q6' ) ), array( 'Q200', 'Q201' ), 'subclass' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), 'check should not comply' );
    }

    // edge cases

    // todo

}