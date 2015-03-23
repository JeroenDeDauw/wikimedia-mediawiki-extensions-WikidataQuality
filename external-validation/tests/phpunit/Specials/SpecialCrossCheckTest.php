<?php

namespace WikidataQuality\ExternalValidation\Tests\Specials\SpecialCrossCheck;

use Wikibase\Test\SpecialPageTestBase;
use WikidataQuality\ExternalValidation\Specials\SpecialCrossCheck;
use DateTime;
use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;
use Wikibase\Lib\Store\EntityLookup;

/**
 * @covers WikidataQuality\ExternalValidation\Specials\SpecialCrossCheck
 *
 * @group Database
 *
 * @author BP2014N1
 * @license GNU GPL v2+exte
 */
class SpecialCrossCheckTest extends SpecialPageTestBase
{
    /**
     * @var EntityLookup
     */
    private $entityLookup;

    /**
     * Array of test items
     * @var array
     */
    private $items;

    /**
     * DumpMetaInformation instance for testing
     * @var DumpMetaInformation
     */
    private $dumpMetaInformation;

    public function __construct( $name = null, $data = array(), $dataName = null )
    {
        parent::__construct( $name, $data, $dataName );

        // Create entity lookup
        $this->entityLookup = new JsonFileEntityLookup( __DIR__ . '/../CrossCheck/testdata' );

        // Get items
        $this->items = array(
            'Q1' => $this->entityLookup->getEntity( new ItemId( 'Q1' ) ),
            'Q2' => $this->entityLookup->getEntity( new ItemId( 'Q2' ) ),
            'Q3' => null
        );

        // Create dump meta information
        $this->dumpMetaInformation = new DumpMetaInformation(
            1,
            '36578',
            new DateTime( '2015-01-01 00:00:00' ),
            'en',
            'http://www.foo.bar',
            42,
            'CC0' );
    }

    public function setUp()
    {
        parent::setUp();

        // Specify database tables used by this test
        $this->tablesUsed[ ] = DUMP_META_TABLE;
        $this->tablesUsed[ ] = DUMP_DATA_TABLE;
    }

    public function tearDown()
    {
        unset( $this->entityLookup, $this->items, $this->dumpMetaInformation );

        parent::tearDown();
    }

    /**
     * Adds temporary test data to database
     * @throws \DBUnexpectedError
     */
    public function addDBData()
    {
        // Truncate tables
        $this->db->delete(
            DUMP_META_TABLE,
            '*'
        );
        $this->db->delete(
            DUMP_DATA_TABLE,
            '*'
        );

        // Insert external test data
        $this->dumpMetaInformation->save( $this->db );

        $this->db->insert(
            DUMP_DATA_TABLE,
            array(
                array(
                    'dump_id' => 1,
                    'identifier_pid' => 227,
                    'external_id' => '119033364',
                    'pid' => 1,
                    'external_value' => 'foo'
                ),
                array(
                    'dump_id' => 1,
                    'identifier_pid' => 227,
                    'external_id' => '119033364',
                    'pid' => 2,
                    'external_value' => 'baz'
                ),
                array(
                    'dump_id' => 1,
                    'identifier_pid' => 227,
                    'external_id' => '119033364',
                    'pid' => 3,
                    'external_value' => 'foobar'
                ),
                array(
                    'dump_id' => 1,
                    'identifier_pid' => 227,
                    'external_id' => '121649091',
                    'pid' => 1,
                    'external_value' => 'bar'
                ),
                array(
                    'dump_id' => 2,
                    'identifier_pid' => 434,
                    'external_id' => 'e9ed318d-8cc5-4cf8-ab77-505e39ab6ea4',
                    'pid' => 1,
                    'external_value' => 'foobar'
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

        $cases['valid input - not existing item'] = array( 'Q99999999', array(), 'en', $matchers );

        unset( $matchers['error'] );

        #$cases['valid input - existing item without statements'] = array( 'Q2', array(), 'en', $matchers );
        #$cases['valid input - existing item with statements'] = array( 'Q30', array(), 'en', $matchers );

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
        foreach( $matchers as $key => $matcher ) {
            $this->assertTag( $matcher, $output, "Failed to match html output with tag '{$key}'" );
        }
    }

}
 