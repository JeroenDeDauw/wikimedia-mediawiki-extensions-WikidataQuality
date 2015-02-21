<?php

namespace WikidataQuality\ConstraintReport\Test\QualifierChecker;

use Wikibase\DataModel\Entity\PropertyId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\QualifierChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;
use Wikibase\DataModel\DeserializerFactory;
use DataValues\Deserializers\DataValueDeserializer;
use Wikibase\DataModel\Entity\BasicEntityIdParser;

class QualifierCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $helper;
    private $qualifiersList;

    protected function setUp() {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->qualifiersList = '{{P|580}}, {{P|582}}, {{P|1365}}, {{P|1366}}, {{P|642}}, {{P|805}}';
    }

    protected function tearDown() {
        unset($this->helper);
        unset($this->qualifiersList);
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

    private function getFirstStatement( $entity )
    {
        foreach( $entity->getStatements() as $statement ) {
            return $statement;
        }
    }

    public function testQualifierConstraintQualifierProperty()
    {
        $file = __DIR__ . './Q1.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q1' ];
        $entity = $this->getEntity( $json );
        $qualifierChecker = new QualifierChecker( $entity->getStatements(), $this->helper );

        $checkResult = $qualifierChecker->checkQualifierConstraint( 'P580', 'Q1384' );
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

    public function testQualifiersConstraint()
    {
        $file = __DIR__ . './Q2.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q2' ];
        $entity = $this->getEntity( $json );
        $qualifierChecker = new QualifierChecker( $entity->getStatements(), $this->helper );

        $checkResult = $qualifierChecker->checkQualifiersConstraint( 'P39', 'Q11696', $this->getFirstStatement( $entity ),  $this->qualifiersList);
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

    public function testQualifiersConstraintToManyQualifiers()
    {
        $file = __DIR__ . './Q3.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q3' ];
        $entity = $this->getEntity( $json );
        $qualifierChecker = new QualifierChecker( $entity->getStatements(), $this->helper );
        $checkResult = $qualifierChecker->checkQualifiersConstraint( 'P39', 'Q11696', $this->getFirstStatement( $entity ),  $this->qualifiersList);
        $this->assertEquals( 'violation', $checkResult->getStatus(), "check should not comply" );
    }

}