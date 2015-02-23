<?php

namespace WikidataQuality\ConstraintReport\Test\RangeChecker;

use Wikibase\DataModel\Entity\PropertyId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\RangeChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use Wikibase\DataModel\DeserializerFactory;
use DataValues\Deserializers\DataValueDeserializer;
use Wikibase\DataModel\Entity\BasicEntityIdParser;

class RangeCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $rangeChecker;
    private $helper;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
    }

    protected function tearDown() {
        unset($this->rangeChecker);
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

    public function testCheckRangeConstraintWithinRange()
    {
        $file = __DIR__ . './Q1.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q1' ];
        $entity = $this->getEntity( $json );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkRangeConstraint( new PropertyId( 'P1457' ), 3.1415926536, 0, 10 );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckRangeConstraintTooSmall()
    {
        $file = __DIR__ . './Q2.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q2' ];
        $entity = $this->getEntity( $json );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkRangeConstraint( new PropertyId( 'P1457' ), 42, 100, 1000 );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testCheckRangeConstraintTooBig()
    {
        $file = __DIR__ . './Q3.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q3' ];
        $entity = $this->getEntity( $json );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkRangeConstraint( new PropertyId( 'P1457' ), 3.1415926536, 0, 1 );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testCheckDiffWithinRangeConstraintWithinRange()
    {
        $file = __DIR__ . './Q4.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q4' ];
        $entity = $this->getEntity( $json );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkDiffWithinRangeConstraint( new PropertyId( 'P570' ), '+00000001970-01-01T00:00:00Z', new PropertyId( 'P569' ), 0, 150 );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testCheckDiffWithinRangeConstraintTooSmall()
    {
        $file = __DIR__ . './Q5.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q5' ];
        $entity = $this->getEntity( $json );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkDiffWithinRangeConstraint( new PropertyId( 'P570' ), '+00000001970-01-01T00:00:00Z', new PropertyId( 'P569' ), 0, 150 );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testCheckDiffWithinRangeConstraintTooBig()
    {
        $file = __DIR__ . './Q6.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q6' ];
        $entity = $this->getEntity( $json );
        $this->rangeChecker = new RangeChecker( $entity->getStatements(), $this->helper );

        $checkResult = $this->rangeChecker->checkDiffWithinRangeConstraint( new PropertyId( 'P570' ), '+00000001970-01-01T00:00:00Z', new PropertyId( 'P569' ), 50, 150 );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

}