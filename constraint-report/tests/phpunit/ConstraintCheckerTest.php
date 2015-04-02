<?php

namespace WikidataQuality\ConstraintReport\Test\ConstraintChecker;

use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ConstraintReport\ConstraintCheck\ConstraintChecker;
use WikidataQuality\Tests\Helper\JsonFileEntityLookup;

/**
 * @covers WikidataQuality\ConstraintReport\ConstraintCheck\ConstraintChecker
 *
 * @group Database
 *
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Helper\ConstraintReportHelper
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Checker\RangeChecker
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Checker\ValueCountChecker
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Checker\OneOfChecker
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Checker\CommonsLinkChecker
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Checker\ConnectionChecker
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Checker\FormatChecker
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Checker\QualifierChecker
 * @uses WikidataQuality\ConstraintReport\ConstraintCheck\Checker\TypeChecker
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class ConstraintCheckerTest extends \MediaWikiTestCase {

    private $constraintChecker;
    private $lookup;

    protected function setUp() {
        parent::setUp();
        $this->lookup = new JsonFileEntityLookup( __DIR__ );
        $this->constraintChecker = new ConstraintChecker( $this->lookup );

        // specify database tables used by this test
        $this->tablesUsed[ ] = CONSTRAINT_TABLE;
    }

    protected function tearDown() {
        unset( $this->lookup );
        unset( $this->constraintChecker );
        parent::tearDown();
    }

    /**
     * Adds temporary test data to database.
     * @throws \DBUnexpectedError
     */
    public function addDBData() {
        $this->db->delete(
            CONSTRAINT_TABLE,
            '*'
        );

        // Adds every type of constraint once to constraints table.
        // Each constraint belongs to the same property.
        $this->db->insert(
            CONSTRAINT_TABLE,
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'Commons link',
                    'namespace' => 'File',
                    'known_exception' => 'Q5'
                )
            )
        );

        $this->db->insert(
            CONSTRAINT_TABLE,
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'Conflicts with',
                    'property' => 'P2'
                ),
                array(
                    'pid' => 1,
                    'constraint_name' => 'Inverse',
                    'property' => 'P2'
                ),
                array(
                    'pid' => 1,
                    'constraint_name' => 'Qualifiers',
                    'property' => 'P2,P3'
                )
            )
        );

        $this->db->insert(
            CONSTRAINT_TABLE,
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'Diff within range',
                    'property' => 'P2',
                    'minimum_quantity' => 0,
                    'maximum_quantity' => 150,
                )
            )
        );

        $this->db->insert(
            CONSTRAINT_TABLE,
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'Format',
                    'pattern' => '[0-9]'
                )
            )
        );

        $this->db->insert(
            CONSTRAINT_TABLE,
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'Multi value'
                ),
                array(
                    'pid' => 1,
                    'constraint_name' => 'Unique value'
                ),
                array(
                    'pid' => 1,
                    'constraint_name' => 'Single value'
                ),
                array(
                    'pid' => 1,
                    'constraint_name' => 'Symmetric'
                ),
                array(
                    'pid' => 1,
                    'constraint_name' => 'Qualifier'
                )
            )
        );

        $this->db->insert(
            CONSTRAINT_TABLE,
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'One of',
                    'item' => 'Q2,Q3'
                )
            )
        );

        $this->db->insert(
            CONSTRAINT_TABLE,
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'Range',
                    'minimum_quantity' => 0,
                    'maximum_quantity' => 2015
                )
            )
        );

        $this->db->insert(
            CONSTRAINT_TABLE,
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'Target required claim',
                    'property' => 'P2',
                    'item' => 'Q2'
                ),
                array(
                    'pid' => 1,
                    'constraint_name' => 'Item',
                    'property' => 'P2',
                    'item' => 'Q2,Q3'
                )
            )
        );

        $this->db->insert(
            CONSTRAINT_TABLE,
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'Type',
                    'class' => 'Q2,Q3',
                    'relation' => 'instance'
                ),
                array(
                    'pid' => 1,
                    'constraint_name' => 'Value type',
                    'class' => 'Q2,Q3',
                    'relation' => 'instance'
                )
            )
        );

        $this->db->insert(
            CONSTRAINT_TABLE,
            array(
                array(
                    'pid' => 3,
                    'constraint_name' => 'Is not inside'
                )
            )
        );
    }

    public function testExecute() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $result = $this->constraintChecker->execute( $entity );
        $this->assertEquals( 17, count( $result ), 'Every constraint should be represented by one result' );
    }

    public function testExecuteWithoutEntity() {
        $result = $this->constraintChecker->execute( null );
        $this->assertEquals( null, $result, 'Should return null' );
    }

    public function testExecuteDoesNotCrashWhenResultIsEmpty() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q2' ) );
        $result = $this->constraintChecker->execute( $entity );
        $this->assertEquals( 0, count( $result ), 'Should be empty' );
    }

    public function testExecuteWithConstraintThatDoesNotBelongToCheckedConstraints() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q3' ) );
        $result = $this->constraintChecker->execute( $entity );
        $this->assertEquals( 1, count( $result ), 'Should be one result' );
        $this->assertEquals( 'todo', $result[0]->getStatus(), 'Should be marked as a todo' );
    }

    public function testExecuteDoesNotCrashWhenStatementHasNovalue() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q4' ) );
        $result = $this->constraintChecker->execute( $entity );
        $this->assertEquals( 0, count( $result ), 'Should be empty' );
    }

    public function testExecuteWithKnownException() {
        $entity = $this->lookup->getEntity( new ItemId( 'Q5' ) );
        $result = $this->constraintChecker->execute( $entity );
        $this->assertEquals( 'exception', $result[0]->getStatus(), 'Should be an exception' );
    }

}