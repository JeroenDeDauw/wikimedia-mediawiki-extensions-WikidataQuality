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
    private $statements;
    private $helper;
    private $propertyId;

    protected function setUp() {
        parent::setUp();

        $file = __DIR__ . './Q1.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q1' ];
        $entity = $this->getEntity( $json );
        $this->statements = $entity->getStatements();

        $this->helper = new ConstraintReportHelper();
        $this->valueCountChecker = new ValueCountChecker( $this->statements, $this->helper );

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

    public function testCheckSingleValueConstraint()
    {
        $checkResult = $this->valueCountChecker->checkSingleValueConstraint( $this->propertyId, 'Q1384' );
        $this->assertEquals( 'compliance', $checkResult->getStatus(), "check should comply" );
    }

}