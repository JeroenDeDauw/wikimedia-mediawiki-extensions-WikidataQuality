<?php

namespace WikidataQuality\ConstraintReport\Test\ValueCountChecker;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\ValueCountChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use Wikibase\DataModel\DeserializerFactory;
use DataValues\Deserializers\DataValueDeserializer;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;

class ValueCountCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $helper;
    private $propertyId;
    private $lookup;

    protected function setUp() {
        parent::setUp();

        $this->helper = new ConstraintReportHelper();
        $this->singlePropertyId = new PropertyId( 'P36' );
        $this->multiPropertyId = new PropertyId( 'P161' );
        $this->lookup = new JsonFileEntityLookup( __DIR__ );
    }

    protected function tearDown() {
        unset($this->helper);
        unset($this->propertyId);
        unset($this->lookup);
        parent::tearDown();
    }

    public function testCheckSingleValueConstraintOne()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $valueCountChecker->checkSingleValueConstraint( $this->singlePropertyId, 'Q1384' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckSingleValueConstraintTwo()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q2' ) );
        $valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $valueCountChecker->checkSingleValueConstraint( $this->singlePropertyId, 'Q1384' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testCheckSingleValueConstraintTwoButOneDeprecated()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q3' ) );
        $valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $valueCountChecker->checkSingleValueConstraint( $this->singlePropertyId, 'Q1384' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckMultiValueConstraintOne()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $valueCountChecker->checkMultiValueConstraint( $this->multiPropertyId, 'Q207' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testCheckMultiValueConstraintTwo()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $valueCountChecker->checkMultiValueConstraint( $this->multiPropertyId, 'Q207' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckMultiValueConstraintTwoButOneDeprecated()
    {
        $entity = $this->lookup->getEntity( new ItemId( 'Q6' ) );
        $valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $valueCountChecker->checkMultiValueConstraint( $this->multiPropertyId, 'Q409' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

}