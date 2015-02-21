<?php

namespace WikidataQuality\ConstraintReport\Test\TypeChecker;

use Wikibase\DataModel\Entity\PropertyId;
use WikidataQuality\ConstraintReport\ConstraintCheck\Checker\TypeChecker;
use WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper;

class TypeCheckerTest {

    private $helper;
    private $lookup;

    protected function setUp()
    {
        parent::setUp();
        $this->helper = new ConstraintReportHelper();
        $this->lookup = WikibaseRepo::getDefaultInstance()->getEntityLookup();
    }

    protected function tearDown()
    {
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

    public function testCheckTypeConstraintValid()
    {
        // Q1 ist TestItem with a statement date of birth: 1.1.1970 and instance of: person
        $file = __DIR__ . './Q1.json';
        $json = json_decode(file_get_contents($file), true)[ 'entities' ][ 'Q1' ];
        $entity = $this->getEntity( $json );
        $typeChecker = new TypeChecker( $entity->getStatements(), $this->lookup, $this->helper);
        $this->assertEquals('compliance', $typeChecker->checkTypeConstraint( 569, "irrelevant", $entity->getStatements(), null, 'Q215627,Q39201', 'instance' )->getStatus(), 'check should comply');
    }

}