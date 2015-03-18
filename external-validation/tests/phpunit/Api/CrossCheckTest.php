<?php

namespace WikidataQuality\ExternalValidation\Tests\Api;

use DataValues\StringValue;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\Lib\ClaimGuidGenerator;
use Wikibase\Test\Api\WikibaseApiTestCase;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;
use WikidataQuality\ExternalValidation\DumpMetaInformation;


/**
 * @covers WikidataQuality\ExternalValidation\Api\CrossCheck
 * @group Database
 * @group API
 * @group medium
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CrossCheckTest extends WikibaseApiTestcase
{
    /** @var EntityId[] */
    private static $idMap;

    /** @var bool */
    private static $hasSetup;

    protected function setup() {
        parent::setup();
        $this->tablesUsed[] = DUMP_META_TABLE;
        $this->tablesUsed[] = DUMP_DATA_TABLE;
    }

    protected function tearDown() {
        parent::tearDown();
    }

    public function testExecuteInvalidParams() {
        $params = array(
            'action' => 'wdqcrosscheck',
            'entities' => 'Q1',
            'claims' => 'randomClaimGuid'
        );
        $this->setExpectedException( 'UsageException', 'Either provide the ids of entities or ids of claims, that should be cross-checked.' );
        $this->doApiRequest($params);
    }

    public function testExecuteMissingParams() {
        $params = array(
            'action' => 'wdqcrosscheck'
        );
        $this->setExpectedException( 'UsageException', 'A parameter that is required was missing (Either provide the ids of entities or ids of claims, that should be cross-checked.)' );
        $this->doApiRequest($params);
    }

    public function testExecuteWholeItem() {
        $params = array(
            'action' => 'wdqcrosscheck',
            'entities' => self::$idMap['Q1']
        );
        $result = $this->doApiRequest( $params );
        $entityIdQ1 = self::$idMap['Q1']->getSerialization();
        $entityIdP1 = self::$idMap['P1']->getSerialization();
        $entityIdP2 = self::$idMap['P2']->getSerialization();
        $this->assertArrayHasKey( $entityIdQ1, $result[0]['results']);
        $this->assertArrayHasKey( $entityIdP1, $result[0]['results'][$entityIdQ1]);
        $this->assertArrayHasKey( $entityIdP2, $result[0]['results'][$entityIdQ1]);
    }

    public function testExecutePropertyFilter() {
        $params = array(
            'action' => 'wdqcrosscheck',
            'entities' => self::$idMap['Q1'],
            'properties' => self::$idMap['P1']
        );
        $result = $this->doApiRequest( $params );
        $entityIdQ1 = self::$idMap['Q1']->getSerialization();
        $entityIdP1 = self::$idMap['P1']->getSerialization();
        $entityIdP2 = self::$idMap['P2']->getSerialization();
        $this->assertArrayHasKey( $entityIdQ1, $result[0]['results']);
        $this->assertArrayHasKey( $entityIdP1, $result[0]['results'][$entityIdQ1]);
        $this->assertArrayNotHasKey( $entityIdP2, $result[0]['results'][$entityIdQ1]);
    }

    public function addDBData() {
        if (!self::$hasSetup) {
            $store = WikibaseRepo::getDefaultInstance()->getEntityStore();

            $propertyP1 = Property::newFromType( 'string' );
            $store->saveEntity( $propertyP1, 'TestEntityP1', $GLOBALS['wgUser'], EDIT_NEW );
            self::$idMap['P1'] = $propertyP1->getId();

            $propertyP2 = Property::newFromType( 'string' );
            $store->saveEntity( $propertyP2, 'TestEntityP2', $GLOBALS['wgUser'], EDIT_NEW );
            self::$idMap['P2'] = $propertyP2->getId();

            $propertyP3 = Property::newFromType( 'string' );
            $store->saveEntity( $propertyP3, 'TestEntityP3', $GLOBALS['wgUser'], EDIT_NEW );
            self::$idMap['P3'] = $propertyP3->getId();

            $itemQ1 = new Item();
            $store->saveEntity( $itemQ1, 'TestEntityQ1', $GLOBALS['wgUser'], EDIT_NEW );
            self::$idMap['Q1'] = $itemQ1->getId();

            $claimGuidGenerator = new ClaimGuidGenerator();

            $dataValue = new StringValue( 'foo' );
            $snak = new PropertyValueSnak( self::$idMap['P1'], $dataValue );
            $claim = new Claim( $snak );
            $claimGuid = $claimGuidGenerator->newGuid( self::$idMap['Q1'] );
            $claim->setGuid( $claimGuid );
            $statement = new Statement( $claim );
            $itemQ1->addClaim( $statement );

            $dataValue = new StringValue( 'baz' );
            $snak = new PropertyValueSnak( self::$idMap['P2'], $dataValue );
            $claim = new Claim( $snak );
            $claimGuid = $claimGuidGenerator->newGuid( self::$idMap['Q1'] );
            $claim->setGuid( $claimGuid );
            $statement = new Statement( $claim );
            $itemQ1->addClaim( $statement );

            $dataValue = new StringValue( '1234' );
            $snak = new PropertyValueSnak( self::$idMap['P3'], $dataValue );
            $claim = new Claim( $snak );
            $claimGuid = $claimGuidGenerator->newGuid( self::$idMap['Q1'] );
            $claim->setGuid( $claimGuid );
            $statement = new Statement( $claim );
            $itemQ1->addClaim( $statement );

            $store->saveEntity( $itemQ1, 'TestEntityQ1', $GLOBALS['wgUser'], EDIT_UPDATE );
            self::$idMap['Q1'] = $itemQ1->getId();

            self::$hasSetup = true;
        }
        // Truncate tables
        $this->db->delete(
            DUMP_META_TABLE,
            '*'
        );
        $this->db->delete(
            DUMP_DATA_TABLE,
            '*'
        );

        // Create dump meta information
        $dumpMetaInformation = new DumpMetaInformation(
            '1',
            '36578',
            new \DateTime( '2015-01-01 00:00:00' ),
            'en',
            'http://www.foo.bar',
            42,
            'CC0' );

        // Insert external test data
        $dumpMetaInformation->save( $this->db );

        $this->db->insert(
            DUMP_DATA_TABLE,
            array(
                array(
                    'dump_id' => '1',
                    'identifier_pid' => self::$idMap['P3']->getNumericId(),
                    'external_id' => '1234',
                    'pid' => self::$idMap['P1']->getNumericId(),
                    'external_value' => 'foo'
                ),
                array(
                    'dump_id' => '1',
                    'identifier_pid' => self::$idMap['P3']->getNumericId(),
                    'external_id' => '1234',
                    'pid' => self::$idMap['P2']->getNumericId(),
                    'external_value' => 'bar'
                )
            )
        );
    }

}