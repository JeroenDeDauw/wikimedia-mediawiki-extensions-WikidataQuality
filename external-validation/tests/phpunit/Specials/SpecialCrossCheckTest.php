<?php

namespace WikidataQuality\ExternalValidation\Tests\Specials\SpecialCrossCheck;

use Wikibase\Test\SpecialPageTestBase;
use WikidataQuality\ExternalValidation\Specials\SpecialCrossCheck;
use DateTime;
use DataValues\StringValue;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\Lib\ClaimGuidGenerator;
use Wikibase\Repo\WikibaseRepo;
use WikidataQuality\ExternalValidation\DumpMetaInformation;

/**
 * @covers WikidataQuality\ExternalValidation\Specials\SpecialCrossCheck
 *
 * @group Database
 * @group medium
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\CrossChecker
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResultList
 *
 * @author BP2014N1
 * @license GNU GPL v2+exte
 */
class SpecialCrossCheckTest extends SpecialPageTestBase
{
    /**
     * Id of a item that (hopefully) does not exist.
     */
    const NOT_EXISTENT_ITEM_ID = 'Q5678765432345678';

    /**
     * @var EntityId[]
     */
    private static $idMap;

    /**
     * @var array
     */
    private static $claimGuids = array();

    /**
     * @var bool
     */
    private static $hasSetup;

    protected function setUp()
    {
        parent::setUp();
        $this->tablesUsed[ ] = DUMP_META_TABLE;
        $this->tablesUsed[ ] = DUMP_DATA_TABLE;
    }

    /**
     * Adds temporary test data to database
     * @throws \DBUnexpectedError
     */
    public function addDBData()
    {
        if ( !self::$hasSetup ) {
            $store = WikibaseRepo::getDefaultInstance()->getEntityStore();

            $propertyP1 = Property::newFromType( 'string' );
            $store->saveEntity( $propertyP1, 'TestEntityP1', $GLOBALS[ 'wgUser' ], EDIT_NEW );
            self::$idMap[ 'P1' ] = $propertyP1->getId();

            $propertyP2 = Property::newFromType( 'string' );
            $store->saveEntity( $propertyP2, 'TestEntityP2', $GLOBALS[ 'wgUser' ], EDIT_NEW );
            self::$idMap[ 'P2' ] = $propertyP2->getId();

            $propertyP3 = Property::newFromType( 'string' );
            $store->saveEntity( $propertyP3, 'TestEntityP3', $GLOBALS[ 'wgUser' ], EDIT_NEW );
            self::$idMap[ 'P3' ] = $propertyP3->getId();

            $itemQ1 = new Item();
            $store->saveEntity( $itemQ1, 'TestEntityQ1', $GLOBALS[ 'wgUser' ], EDIT_NEW );
            self::$idMap[ 'Q1' ] = $itemQ1->getId();

            $claimGuidGenerator = new ClaimGuidGenerator();

            $dataValue = new StringValue( 'foo' );
            $snak = new PropertyValueSnak( self::$idMap[ 'P1' ], $dataValue );
            $claim = new Claim( $snak );
            $claimGuid = $claimGuidGenerator->newGuid( self::$idMap[ 'Q1' ] );
            self::$claimGuids[ 'P1' ] = $claimGuid;
            $claim->setGuid( $claimGuid );
            $statement = new Statement( $claim );
            $itemQ1->addClaim( $statement );

            $dataValue = new StringValue( 'baz' );
            $snak = new PropertyValueSnak( self::$idMap[ 'P2' ], $dataValue );
            $claim = new Claim( $snak );
            $claimGuid = $claimGuidGenerator->newGuid( self::$idMap[ 'Q1' ] );
            self::$claimGuids[ 'P2' ] = $claimGuid;
            $claim->setGuid( $claimGuid );
            $statement = new Statement( $claim );
            $itemQ1->addClaim( $statement );

            $dataValue = new StringValue( '1234' );
            $snak = new PropertyValueSnak( self::$idMap[ 'P3' ], $dataValue );
            $claim = new Claim( $snak );
            $claimGuid = $claimGuidGenerator->newGuid( self::$idMap[ 'Q1' ] );
            self::$claimGuids[ 'P3' ] = $claimGuid;
            $claim->setGuid( $claimGuid );
            $statement = new Statement( $claim );
            $itemQ1->addClaim( $statement );

            $store->saveEntity( $itemQ1, 'TestEntityQ1', $GLOBALS[ 'wgUser' ], EDIT_UPDATE );

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
                    'identifier_pid' => self::$idMap[ 'P3' ]->getNumericId(),
                    'external_id' => '1234',
                    'pid' => self::$idMap[ 'P1' ]->getNumericId(),
                    'external_value' => 'foo'
                ),
                array(
                    'dump_id' => '1',
                    'identifier_pid' => self::$idMap[ 'P3' ]->getNumericId(),
                    'external_id' => '1234',
                    'pid' => self::$idMap[ 'P2' ]->getNumericId(),
                    'external_value' => 'bar'
                )
            )
        );
    }

    protected function newSpecialPage() {
        $page = new SpecialCrossCheck();

        $languageNameLookup = $this->getMock( 'Wikibase\Lib\LanguageNameLookup' );
        $languageNameLookup->expects( $this->any() )
            ->method( 'getName' )
            ->will( $this->returnValue( 'LANGUAGE NAME' ) );

        return $page;
    }


    public function requestProvider()
    {
        $cases = array();
        $matchers = array();

        // Empty input
        $matchers['entityId'] = array(
            'tag' => 'input',
            'attributes' => array(
                'id' => 'wdq-crosscheck-entityid',
                'placeholder' => 'Qxx',
                'name' => 'entityId',
                'class' => 'mw-ui-input'
            )
        );

        $matchers['submit'] = array(
            'tag' => 'input',
            'attributes' => array(
                'id' => 'wdq-crosscheck-submit',
                'type' => 'submit',
                'value' => 'Cross-Check',
                'name' => 'submit'
            )
        );

        $cases['empty'] = array('', array(), null, $matchers);

        // Invalid input (en)
        $matchers['error'] = array(
            'tag' => 'p',
            'attributes' => array(
                'class' => 'wdq-crosscheck-error'
            ),
            'content' => 'The given input string is not a string that could be parsed to an entityId.'
        );

        $cases['invalid input 1'] = array( 'Qwertz', array(), 'en', $matchers );
        $cases['invalid input 2'] = array( '300', array(), 'en', $matchers );

        // Valid input (en)
        unset( $matchers['error'] );

        $matchers['result for'] = array(
            'tag' => 'h3',
            'content' => 'Result for'
        );

        $matchers['error'] = array(
            'tag' => 'p',
            'attributes' => array(
                'class' => 'wdq-crosscheck-error'
            ),
            'content' => 'Item does not exist!'
        );

        $cases['valid input - not existing item'] = array( self::NOT_EXISTENT_ITEM_ID, array(), 'en', $matchers );

        unset( $matchers['error'] );

        $cases['valid input - existing item with statements'] = array( '' . self::$idMap[ 'Q1' ], array(), 'en', $matchers );
        #$cases['valid input - existing item without statements'] = array( 'Q3', array(), 'en', $matchers );

        return $cases;
    }

    /**
     * @dataProvider requestProvider
     *
     * @param string $sub The subpage parameter to call the page with
     * @param WebRequest|null $request Web request that may contain URL parameters, etc
     * @param string $userLanguage The language code which should be used in the context of this special page
     * @param $matchers
     */
    public function testExecute( $sub, $request, $userLanguage, $matchers ) {
        $request = new \FauxRequest( $request );

        list( $output, ) = $this->executeSpecialPage( $sub, $request, $userLanguage );
        echo self::$idMap[ 'Q1' ];
        echo '######' . $output;
        foreach( $matchers as $key => $matcher ) {
            $this->assertTag( $matcher, $output, "Failed to match html output with tag '{$key}'" );
        }
    }

}
 