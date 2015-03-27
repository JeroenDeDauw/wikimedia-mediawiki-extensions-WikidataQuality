<?php

namespace WikidataQuality\ExternalValidation\UpdateTable;


use DateTime;
use DateTimeZone;
use Wikibase\DataModel\Entity\ItemId;
use WikidataQuality\ExternalValidation\DumpMetaInformation;


/**
 * Class Importer
 * @package WikidataQuality\ExternalValidation\UpdateTable
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class Importer
{
    /**
     * @var ImportContext
     */
    private $importContext;


    /**
     * @param ImportContext $importContext
     */
    public function __construct( $importContext )
    {
        $this->importContext = $importContext;
    }

    /**
     * Starts the whole import process
     */
    function import()
    {
        // Establish database connection
        $db = $this->establishDbConnection();

        // Insert meta data information
        $dumpItemIds = $this->insertMetaInformation( $db );

        // Remove old database entries
        $this->deleteOldDatabaseEntries( $db, $dumpItemIds );

        // Insert external values
        $this->insertExternalValues( $db );

        // Reuse database connection
        $this->reuseDbConnection( $db );
    }

    protected function deleteOldDatabaseEntries( $db, $dumpItemIds )
    {
        global $wgDBtype;
        $tableName = $this->importContext->getTargetTableName();

        // Check, if table exists
        if ( !$db->tableExists( $tableName ) ) {
            if ( !$this->importContext->isQuiet() ) {
                print "$tableName table does not exist.\nExecuting core/maintenance/update.php may help.\n";
            }
            return;
        }

        // Delete all entries
        if ( !$this->importContext->isQuiet() ) {
            print "Removing old entries\n";
        }

        foreach ( $dumpItemIds as $dumpItemId ) {
            if ( $wgDBtype === 'sqlite' ) {
                $db->delete( $tableName, 'dump_id=' . $dumpItemId->getNumericId() );
            } else {
                do {
                    $db->commit( __METHOD__, 'flush' );
                    wfWaitForSlaves();
                    if ( !$this->importContext->isQuiet() ) {
                        print "Deleting a batch\n";
                    }
                    $table = $db->tableName( $tableName );
                    $batchSize = $this->importContext->getBatchSize();
                    $db->query( "DELETE FROM $table WHERE dump_item_id=" . $dumpItemId->getNumericId() . " LIMIT $batchSize" );
                } while ( $db->affectedRows() > 0 );
            }
        }
    }

    /**
     * Establishes a database connection using the load balancer
     * @return \DatabaseBase
     * @throws \MWException
     */
    protected function establishDbConnection()
    {
        $loadBalancer = $this->importContext->getLoadBalancer();
        $db = $loadBalancer->getConnection( DB_MASTER );

        return $db;
    }

    /**
     * Mark databsae connection as being available for reuse
     * @param \DatabaseBase $db
     */
    protected function reuseDbConnection( $db )
    {
        $loadBalancer = $this->importContext->getLoadBalancer();
        $loadBalancer->reuseConnection( $db );
    }

    /**
     * Inserts meta information stored in csv file into database.
     * @param \DatabaseBase $db
     * @return array
     */
    protected function insertMetaInformation( $db )
    {
        // Open csv file
        $csvFile = fopen( $this->importContext->getMetaInformationFilePath(), 'rb' );

        $dumpItemIds = array();
        while ( $data = fgetcsv( $csvFile ) ) {
            $metaInformation = new DumpMetaInformation(
                new ItemId( 'Q' . $data[ 0 ] ),
                new DateTime( $data[ 1 ], new DateTimeZone( 'UTC' ) ),
                $data[ 2 ],
                $data[ 3 ],
                $data[ 4 ],
                $data[ 5 ]
            );
            $metaInformation->save( $db );

            $dumpItemIds[] = $metaInformation->getSourceItemId();
        }

        // Close csv file
        fclose( $csvFile );

        return $dumpItemIds;
    }

    /**
     * Inserts external values stored in csv file into database
     * @param \DatabaseBase $db
     * @param string $dumpId
     */
    protected function insertExternalValues( $db )
    {
        // Open csv file
        $csvFile = fopen( $this->importContext->getEntitiesFilePath(), 'rb' );

        $i = 0;
        $accumulator = array();
        while ( true ) {
            $data = fgetcsv( $csvFile );
            if ( $data == false || ++$i % $this->importContext->getBatchSize() == 0 ) {
                // Write batch into datbase
                $db->commit( __METHOD__, 'flush' );
                wfWaitForSlaves();
                $db->insert( $this->importContext->getTargetTableName(), $accumulator );
                if ( !$this->importContext->isQuiet() ) {
                    print "$i rows inserted\n";
                }

                // Clear accumulator
                $accumulator = array();

                // Stop when no data is read anymore
                if ( $data == false ) {
                    break;
                }
            }

            // Add data of read row to accumulator
            $accumulator[ ] = array(
                'dump_item_id' => $data[ 0 ],
                'identifier_pid' => $data[ 1 ],
                'external_id' => $data[ 2 ],
                'pid' => $data[ 3 ],
                'external_value' => $data[ 4 ],
            );
        }

        // Close csv file
        fclose( $csvFile );
    }
}