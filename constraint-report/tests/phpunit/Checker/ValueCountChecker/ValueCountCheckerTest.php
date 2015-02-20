<?php

namespace WikidataQuality\ConstraintReport\Test\ValueCountChecker;

use Wikibase\DataModel\Entity\PropertyId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\ValueCountChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use Wikibase\DataModel\DeserializerFactory;
use DataValues\Deserializers\DataValueDeserializer;
use Wikibase\DataModel\Entity\BasicEntityIdParser;

class ValueCountCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $valueCountChecker;
    private $helper;
    private $propertyId;

    protected function setUp() {
        parent::setUp();

        $this->helper = new ConstraintReportHelper();
        $this->propertyId = new PropertyId( 'P36' );
    }

    protected function tearDown() {
        unset($this->valueCountChecker);
        unset($this->statements);
        unset($this->helper);
        parent::tearDown();
    }

    private function getEntity( $entityJson )
    {
        if ( $entityJson ) {
            $deserializerFactory = new DeserializerFactory(
                new DataValueDeserializer(
                    array(
                        'boolean' => 'DataValues\BooleanValue',
                        'number' => 'DataValues\NumberValue',
                        'string' => 'DataValues\StringValue',
                        'unknown' => 'DataValues\UnknownValue',
                        'globecoordinate' => 'DataValues\GlobeCoordinateValue',
                        'monolingualtext' => 'DataValues\MonolingualTextValue',
                        'multilingualtext' => 'DataValues\MultilingualTextValue',
                        'quantity' => 'DataValues\QuantityValue',
                        'time' => 'DataValues\TimeValue',
                        'wikibase-entityid' => 'Wikibase\DataModel\Entity\EntityIdValue',
                    )
                ),
                new BasicEntityIdParser()
            );
            return $deserializerFactory->newEntityDeserializer()->deserialize( $entityJson );
        }
    }

    public function testCheckSingleValueConstraintOne()
    {
        $file = __DIR__ . './Q1.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q1' ];
        $entity = $this->getEntity( $json );
        $this->valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->valueCountChecker->checkSingleValueConstraint( $this->propertyId, 'Q1384' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckSingleValueConstraintTwo()
    {
        $file = __DIR__ . './Q2.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q2' ];
        $entity = $this->getEntity( $json );
        $this->valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->valueCountChecker->checkSingleValueConstraint( $this->propertyId, 'Q1384' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testCheckSingleValueConstraintTwoButOneDeprecated()
    {
        $file = __DIR__ . './Q3.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q3' ];
        $entity = $this->getEntity( $json );
        $this->valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->valueCountChecker->checkSingleValueConstraint( $this->propertyId, 'Q1384' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckMultiValueConstraintOne()
    {
        $file = __DIR__ . './Q4.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q4' ];
        $entity = $this->getEntity( $json );
        $this->valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->valueCountChecker->checkMultiValueConstraint( $this->propertyId, 'Q207' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testCheckMultiValueConstraintTwo()
    {
        $file = __DIR__ . './Q5.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q5' ];
        $entity = $this->getEntity( $json );
        $this->valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->valueCountChecker->checkMultiValueConstraint( $this->propertyId, 'Q207' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckMultiValueConstraintTwoButOneDeprecated()
    {
        $file = __DIR__ . './Q6.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q6' ];
        $entity = $this->getEntity( $json );
        $this->valueCountChecker = new ValueCountChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->valueCountChecker->checkMultiValueConstraint( $this->propertyId, 'Q207' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

}