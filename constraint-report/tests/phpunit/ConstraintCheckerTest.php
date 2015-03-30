<?php

namespace WikidataQuality\ConstraintReport\Test\ConstraintChecker;
use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ConstraintReport\ConstraintCheck\ConstraintChecker;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;

/**
 * @covers WikidataQuality\ConstraintReport\ConstraintCheck\ConstraintChecker
 *
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class ConstraintCheckerTest extends \MediaWikiTestCase {

    private $constraintChecker;

    protected function setUp() {
        parent::setUp();
        $this->lookup = new JsonFileEntityLookup( __DIR__ );
        $this->constraintChecker = new ConstraintChecker( $this->lookup );
    }

    protected function tearDown() {
        unset( $this->lookup );
        parent::tearDown();
    }

    public function testExecuteNoViolations() {
        //Checks for Item with only statement: Date of birth which has 8 constraints defined
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $result = $this->constraintChecker->execute( $entity );
        $this->assertEquals( 0, count( $result ), 'Only one result' );
        //TODO: This doesn't work
    }
}