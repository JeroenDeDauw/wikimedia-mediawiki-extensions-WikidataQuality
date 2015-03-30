<?php

namespace WikidataQuality\ConstraintReport\Test\ConnectionChecker;

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

}