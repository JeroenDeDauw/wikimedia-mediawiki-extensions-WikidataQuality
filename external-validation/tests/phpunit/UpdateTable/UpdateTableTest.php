<?php

namespace WikidataQuality\ExternalValidation\Tests\UpdateTable;


use WikidataQuality\ExternalValidation\DumpMetaInformation;
use WikidataQuality\ExternalValidation\Maintenance\UpdateTable;


/**
 * @covers WikidataQuality\ExternalValidation\UpdateTable\Importer
 * @covers WikidataQuality\ExternalValidation\UpdateTable\ImportContext
 * @covers WikidataQuality\ExternalValidation\Maintenance\UpdateTable
 *
 * @uses   WikidataQuality\ExternalValidation\DumpMetaInformation
 *
 * @group Database
 * @group medium
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class UpdateTableTestTest extends \MediaWikiTestCase
{
    protected function setup()
    {
        parent::setup();
        $this->tablesUsed[ ] = DUMP_META_TABLE;
        $this->tablesUsed[ ] = DUMP_DATA_TABLE;
    }

    public function addDBData()
    {
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

        // Insert external data
        $this->db->insert(
            DUMP_DATA_TABLE,
            array(
                array(
                    'dump_id' => '1',
                    'identifier_pid' => '227',
                    'external_id' => '1234',
                    'pid' => '31',
                    'external_value' => 'foo'
                ),
                array(
                    'dump_id' => '1',
                    'identifier_pid' => '227',
                    'external_id' => '1234',
                    'pid' => '35',
                    'external_value' => 'bar'
                )
            )
        );
    }


    public function testExecute()
    {
        // Execute script
        $maintenanceScript = new UpdateTable();
        $args = array(
            'entities-file' => __DIR__ . '/testdata/entities.csv',
            'meta-information-file' => __DIR__ . '/testdata/meta.csv',
            'quiet' => true
        );
        $maintenanceScript->loadParamsAndArgs( null, $args, null );
        $maintenanceScript->execute();

        // Run assertions
        $this->assertSelect(
            DUMP_META_TABLE,
            array(
                'source_item_id',
                'import_date',
                'language',
                'source_url',
                'size',
                'license'
            ),
            array( 'row_id=1' ),
            array(
                array(
                    '36578',
                    '2015-03-17 20:53:56',
                    'de',
                    'http://www.foo.bar',
                    '590798465',
                    'CC0 1.0'
                )
            )
        );

        $this->assertSelect(
            DUMP_DATA_TABLE,
            array(
                'count' => 'count(*)'
            ),
            array(),
            array(
                array( '3' )
            )
        );
        $this->assertSelect(
            DUMP_DATA_TABLE,
            array(
                'dump_id',
                'identifier_pid',
                'pid',
                'external_id',
                'external_value'
            ),
            array(),
            array(
                array(
                    '1',
                    '227',
                    '19',
                    '100001718',
                    'Parma'
                ),
                array(
                    '1',
                    '227',
                    '20',
                    '100001718',
                    'Paris'
                ),
                array(
                    '1',
                    '227',
                    '569',
                    '100001718',
                    '01.06.1771'
                )
            )
        );
    }
}
