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

        // Specify database tables used by this test
        $this->tablesUsed[ ] = 'constraints_ready_for_migration';
    }

    protected function tearDown() {
        unset( $this->lookup );
        unset( $this->constraintChecker );
        parent::tearDown();
    }

    /**
     * Adds temporary test data to database
     * @throws \DBUnexpectedError
     */
    public function addDBData()
    {
        $this->db->delete(
            'constraints_ready_for_migration',
            '*'
        );

        // adds every type of constraint once to constraints table
        // each constraint belonging to the same property
        $this->db->insert(
            'constraints_ready_for_migration',
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'Commons link',
                    'namespace' => 'File'
                )
            )
        );

        $this->db->insert(
            'constraints_ready_for_migration',
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
            'constraints_ready_for_migration',
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
            'constraints_ready_for_migration',
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'Format',
                    'pattern' => '[0-9]'
                )
            )
        );

        $this->db->insert(
            'constraints_ready_for_migration',
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
            'constraints_ready_for_migration',
            array(
                array(
                    'pid' => 1,
                    'constraint_name' => 'One of',
                    'item' => 'Q2,Q3'
                )
            )
        );

        $this->db->insert(
            'constraints_ready_for_migration',
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
            'constraints_ready_for_migration',
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
            'constraints_ready_for_migration',
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
    }

    public function testExecuteNoViolations() {
        //Checks for Item with only statement: Date of birth which has 8 constraints defined
        $entity = $this->lookup->getEntity( new ItemId( 'Q1' ) );
        $result = $this->constraintChecker->execute( $entity );
        $this->assertEquals( 17, count( $result ), 'Only one result' );
    }
}