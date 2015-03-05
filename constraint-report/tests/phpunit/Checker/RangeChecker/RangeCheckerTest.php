<?php

namespace WikidataQuality\ConstraintReport\Test\RangeChecker;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\RangeChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use Wikibase\DataModel\DeserializerFactory;
use DataValues\Deserializers\DataValueDeserializer;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;

class RangeCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $helper;
    private $lookup;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->lookup = new JsonFileEntityLookup( __DIR__ );
    }

    protected function tearDown() {
        unset( $this->helper );
        unset( $this->lookup );
        parent::tearDown();
    }

    public function testCheckRangeConstraintWithinRange()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $rangeChecker->checkRangeConstraint( new PropertyId( 'P1457' ), 3.1415926536, 0, 10, null, null );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckRangeConstraintTooSmall()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q2' ) );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkRangeConstraint( new PropertyId( 'P1457' ), 42, 100, 1000, null, null );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testCheckRangeConstraintTooBig()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q3' ) );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkRangeConstraint( new PropertyId( 'P1457' ), 3.141592, 0, 1, null, null );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testCheckDiffWithinRangeConstraintWithinRange()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkDiffWithinRangeConstraint( new PropertyId( 'P570' ), '+00000001970-01-01T00:00:00Z', new PropertyId( 'P569' ), 0, 150 );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckDiffWithinRangeConstraintTooSmall()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkDiffWithinRangeConstraint( new PropertyId( 'P570' ), '+00000001970-01-01T00:00:00Z', new PropertyId( 'P569' ), 50, 150 );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testCheckDiffWithinRangeConstraintTooBig()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q6' ) );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkDiffWithinRangeConstraint( new PropertyId( 'P570' ), '+00000001970-01-01T00:00:00Z', new PropertyId( 'P569' ), 0, 150 );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

}