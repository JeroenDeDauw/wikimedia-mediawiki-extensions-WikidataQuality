<?php

namespace WikidataQuality\ExternalValidation\Tests\CrossCheck;

use DataValues\MonolingualTextValue;
use DataValues\StringValue;
use DateTime;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use WikidataQuality\ExternalValidation\CrossCheck\CrossChecker;
use WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult;
use WikidataQuality\ExternalValidation\DumpMetaInformation;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;


/**
 * @covers WikidataQuality\ExternalValidation\CrossCheck\CrossChecker
 *
 * @group Database
 *
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\DataValueComparer
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Comparer\StringValueComparer
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResult
 * @uses   WikidataQuality\ExternalValidation\CrossCheck\Result\CompareResultList
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CrossCheckerTest extends \MediaWikiTestCase
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
     * @var \DumpMetaInformation
     */
    private $dumpMetaInformation;


    public function __construct( $name = null, $data = array(), $dataName = null )
    {
        parent::__construct( $name, $data, $dataName );

        // Create entity lookup
        $this->entityLookup = new JsonFileEntityLookup( __DIR__ . '/testdata' );

        // Get items
        $this->items = array(
            'Q1' => $this->entityLookup->getEntity( new ItemId( 'Q1' ) ),
            'Q2' => $this->entityLookup->getEntity( new ItemId( 'Q2' ) ),
            'Q3' => null
        );

        // Create dump meta information
        $this->dumpMetaInformation = new DumpMetaInformation(
            '1',
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
            "*"
        );
        $this->db->delete(
            DUMP_DATA_TABLE,
            "*"
        );

        // Insert external test data
        $this->dumpMetaInformation->save( $this->db );

        $this->db->insert(
            DUMP_DATA_TABLE,
            array(
                array(
                    'dump_id' => '1',
                    'identifier_pid' => '227',
                    'external_id' => '119033364',
                    'pid' => '1',
                    'external_value' => 'foo'
                ),
                array(
                    'dump_id' => '1',
                    'identifier_pid' => '227',
                    'external_id' => '119033364',
                    'pid' => '2',
                    'external_value' => 'baz'
                ),
                array(
                    'dump_id' => '1',
                    'identifier_pid' => '227',
                    'external_id' => '119033364',
                    'pid' => '3',
                    'external_value' => 'foobar'
                ),
                array(
                    'dump_id' => '1',
                    'identifier_pid' => '227',
                    'external_id' => '121649091',
                    'pid' => '1',
                    'external_value' => 'bar'
                ),
                array(
                    'dump_id' => '2',
                    'identifier_pid' => '434',
                    'external_id' => 'e9ed318d-8cc5-4cf8-ab77-505e39ab6ea4',
                    'pid' => '1',
                    'external_value' => 'foobar'
                )
            )
        );
    }


    public function testConstruct()
    {
        // Check private fields using reflection
        $crossCheckerReflection = new \ReflectionClass( 'WikidataQuality\ExternalValidation\CrossCheck\CrossChecker' );
        $loadBalancerProperty = $crossCheckerReflection->getProperty( 'loadBalancer' );
        $loadBalancerProperty->setAccessible( true );
        $dbProperty = $crossCheckerReflection->getProperty( 'db' );
        $dbProperty->setAccessible( true );

        // Create CrossChecker with implicit database connection
        $crossChecker = new CrossChecker();
        $this->assertNotNull( $loadBalancerProperty->getValue( $crossChecker ) );
        $this->assertNotNull( $dbProperty->getValue( $crossChecker ) );

        // Create CrossChecker with explicit database connection
        $crossChecker = new CrossChecker( $this->db );
        $this->assertNull( $loadBalancerProperty->getValue( $crossChecker ) );
        $this->assertNotNull( $dbProperty->getValue( $crossChecker ) );
    }


    /**
     * @dataProvider crossCheckEntityDataProvider
     */
    public function testCrossCheckEntity( $entity, $propertyIds, $expectedResults, $expectedException = null )
    {
        // If exception is expected, set it so
        if ( $expectedException ) {
            $this->setExpectedException( $expectedException );
        }

        // Run cross-check
        $crossChecker = $this->getTestCrossChecker();
        $results = $crossChecker->crossCheckEntity( $entity, $propertyIds );

        $this->runResultAssertions( $results, $expectedResults );
    }

    /**
     * Test cases for testCrossCheckEntity
     * @return array
     */
    public function crossCheckEntityDataProvider()
    {
        $language = $this->dumpMetaInformation->getLanguage();

        return array(
            //Crosscheck all properties of Q1
            array(
                $this->items[ 'Q1' ],
                null,
                array(
                    'Q1$c0f25a6f-9e33-41c8-be34-c86a730ff30b' => new CompareResult(
                        new PropertyId( "P1" ),
                        'Q1$c0f25a6f-9e33-41c8-be34-c86a730ff30b',
                        new StringValue( 'foo' ),
                        array( new MonolingualTextValue( $language, 'foo' ) ),
                        false,
                        null,
                        $this->dumpMetaInformation
                    ),
                    'Q1$dd6dcfc9-55e2-4be6-b70c-d22f20f398b7' => new CompareResult(
                        new PropertyId( "P1" ),
                        'Q1$dd6dcfc9-55e2-4be6-b70c-d22f20f398b7',
                        new StringValue( 'bar' ),
                        array( new MonolingualTextValue( $language, 'foo' ) ),
                        true,
                        null,
                        $this->dumpMetaInformation
                    ),
                    'Q1$01636a9a-97a5-478e-bf55-5d9a569c7ce5' => new CompareResult(
                        new PropertyId( "P2" ),
                        'Q1$01636a9a-97a5-478e-bf55-5d9a569c7ce5',
                        new StringValue( 'foobar' ),
                        array( new MonolingualTextValue( $language, 'baz' ) ),
                        true,
                        null,
                        $this->dumpMetaInformation
                    ),
                    'Q1$27ba9958-7151-4673-8956-f8f1d8648d1e' => new CompareResult(
                        new PropertyId( "P3" ),
                        'Q1$27ba9958-7151-4673-8956-f8f1d8648d1e',
                        new StringValue( 'fubar' ),
                        array( new MonolingualTextValue( $language, 'foobar' ) ),
                        true,
                        null,
                        $this->dumpMetaInformation
                    )
                )
            ),
            // Only crosscheck statements of Q1 with P1
            array(
                $this->items[ 'Q1' ],
                new PropertyId( 'P1' ),
                array(
                    'Q1$c0f25a6f-9e33-41c8-be34-c86a730ff30b' => new CompareResult(
                        new PropertyId( "P1" ),
                        'Q1$c0f25a6f-9e33-41c8-be34-c86a730ff30b',
                        new StringValue( 'foo' ),
                        array( new MonolingualTextValue( $language, 'foo' ) ),
                        false,
                        null,
                        $this->dumpMetaInformation
                    ),
                    'Q1$dd6dcfc9-55e2-4be6-b70c-d22f20f398b7' => new CompareResult(
                        new PropertyId( "P1" ),
                        'Q1$dd6dcfc9-55e2-4be6-b70c-d22f20f398b7',
                        new StringValue( 'bar' ),
                        array( new MonolingualTextValue( $language, 'foo' ) ),
                        true,
                        null,
                        $this->dumpMetaInformation
                    )
                )
            ),
            // Only crosscheck statements of Q1 with P1 and P3
            array(
                $this->items[ 'Q1' ],
                array(
                    new PropertyId( 'P1' ),
                    new PropertyId( 'P3' )
                ),
                array(
                    'Q1$c0f25a6f-9e33-41c8-be34-c86a730ff30b' => new CompareResult(
                        new PropertyId( "P1" ),
                        'Q1$c0f25a6f-9e33-41c8-be34-c86a730ff30b',
                        new StringValue( 'foo' ),
                        array( new MonolingualTextValue( $language, 'foo' ) ),
                        false,
                        null,
                        $this->dumpMetaInformation
                    ),
                    'Q1$dd6dcfc9-55e2-4be6-b70c-d22f20f398b7' => new CompareResult(
                        new PropertyId( "P1" ),
                        'Q1$dd6dcfc9-55e2-4be6-b70c-d22f20f398b7',
                        new StringValue( 'bar' ),
                        array( new MonolingualTextValue( $language, 'foo' ) ),
                        true,
                        null,
                        $this->dumpMetaInformation
                    ),
                    'Q1$27ba9958-7151-4673-8956-f8f1d8648d1e' => new CompareResult(
                        new PropertyId( "P3" ),
                        'Q1$27ba9958-7151-4673-8956-f8f1d8648d1e',
                        new StringValue( 'fubar' ),
                        array( new MonolingualTextValue( $language, 'foobar' ) ),
                        true,
                        null,
                        $this->dumpMetaInformation
                    )
                )
            ),
            // Crosscheck Q2, which has two identifier for a single database
            array(
                $this->items[ 'Q2' ],
                null,
                array(
                    'Q1$0adcfe9e-cda1-4f74-bc98-433150e49b53' => new CompareResult(
                        new PropertyId( "P1" ),
                        'Q1$0adcfe9e-cda1-4f74-bc98-433150e49b53',
                        new StringValue( 'foobar' ),
                        array(
                            new MonolingualTextValue( $language, 'foo' ),
                            new MonolingualTextValue( $language, 'bar' )
                        ),
                        true,
                        null,
                        $this->dumpMetaInformation
                    ),
                    'Q1$07c00375-1be7-43a6-ac97-32770f2bb5ac' => new CompareResult(
                        new PropertyId( "P1" ),
                        'Q1$07c00375-1be7-43a6-ac97-32770f2bb5ac',
                        new StringValue( 'bar' ),
                        array(
                            new MonolingualTextValue( $language, 'foo' ),
                            new MonolingualTextValue( $language, 'bar' )
                        ),
                        false,
                        null,
                        $this->dumpMetaInformation
                    ),
                )
            ),
            // Crosscheck non-existent item without property filter
            array(
                $this->items[ 'Q3' ],
                null,
                null
            ),
            // Crosscheck non-existent item with property filter
            array(
                $this->items[ 'Q3' ],
                array(
                    new PropertyId( 'P1' ),
                    new PropertyId( 'P3' )
                ),
                null
            ),
            // Provide invalid arguments
            array(
                $this->items[ 'Q2' ],
                'crap',
                null,
                'InvalidArgumentException'
            ),
            array(
                $this->items[ 'Q2' ],
                array( 'crap' ),
                null,
                'InvalidArgumentException'
            )
        );
    }


    /**
     * @dataProvider crossCheckStatementsDataProvider
     */
    public function testCrossCheckStatements( $entity, $statements, $expectedResults, $expectedException = null )
    {
        // If exception is expected, set it so
        if ( $expectedException ) {
            $this->setExpectedException( $expectedException );
        }

        // Run cross-check
        $crossChecker = $this->getTestCrossChecker();
        $results = $crossChecker->crossCheckStatements( $entity, $statements );

        $this->runResultAssertions( $results, $expectedResults );
    }

    /**
     * Test cases for testCrossCheckStatements
     */
    public function crossCheckStatementsDataProvider()
    {
        $language = $this->dumpMetaInformation->getLanguage();

        return array(
            // Crosscheck single statement of Q1
            array(
                $this->items[ 'Q1' ],
                $this->items[ 'Q1' ]->getStatements()->getWithPropertyId( new PropertyId( 'P1' ) )->toArray()[ 0 ],
                array(
                    'Q1$c0f25a6f-9e33-41c8-be34-c86a730ff30b' => new CompareResult(
                        new PropertyId( "P1" ),
                        'Q1$c0f25a6f-9e33-41c8-be34-c86a730ff30b',
                        new StringValue( 'foo' ),
                        array( new MonolingualTextValue( $language, 'foo' ) ),
                        false,
                        null,
                        $this->dumpMetaInformation
                    )
                )
            ),
            // Crosscheck list of several statements of Q1
            array(
                $this->items[ 'Q1' ],
                $this->items[ 'Q1' ]->getStatements()->getWithPropertyId( new PropertyId( 'P1' ) ),
                array(
                    'Q1$c0f25a6f-9e33-41c8-be34-c86a730ff30b' => new CompareResult(
                        new PropertyId( "P1" ),
                        'Q1$c0f25a6f-9e33-41c8-be34-c86a730ff30b',
                        new StringValue( 'foo' ),
                        array( new MonolingualTextValue( $language, 'foo' ) ),
                        false,
                        null,
                        $this->dumpMetaInformation
                    ),
                    'Q1$dd6dcfc9-55e2-4be6-b70c-d22f20f398b7' => new CompareResult(
                        new PropertyId( "P1" ),
                        'Q1$dd6dcfc9-55e2-4be6-b70c-d22f20f398b7',
                        new StringValue( 'bar' ),
                        array( new MonolingualTextValue( $language, 'foo' ) ),
                        true,
                        null,
                        $this->dumpMetaInformation
                    )
                )
            ),
            // Crosscheck non-existent item
            array(
                $this->items[ 'Q3' ],
                new StatementList(),
                null
            ),
            // Provide invalid arguments
            array(
                $this->items[ 'Q1' ],
                'crap',
                null,
                'InvalidArgumentException'
            ),
            array(
                $this->items[ 'Q2' ],
                $this->items[ 'Q1' ]->getStatements()->getWithPropertyId( new PropertyId( 'P2' ) ),
                null,
                'InvalidArgumentException'
            )
        );
    }


    /**
     * Runs assertions on compare result list.
     * @param CompareResultList $results
     * @param array $expectedResults
     */
    private function runResultAssertions( $results, $expectedResults )
    {
        if ( $results ) {
            foreach ( $results as $result ) {
                $this->assertArrayHasKey( $result->getClaimGuid(), $expectedResults );
                $this->assertEquals( $expectedResults[ $result->getClaimGuid() ], $result );
            }
            $this->assertEquals( count( $expectedResults ), count( $results ) );
        } else {
            $this->assertEquals( $expectedResults, $results );
        }
    }


    /**
     * Returns new CrossChecker instance with temporary database connection.
     * @return CrossChecker
     */
    private function getTestCrossChecker()
    {
        $crossChecker = new CrossChecker( $this->db );

        return $crossChecker;
    }
}