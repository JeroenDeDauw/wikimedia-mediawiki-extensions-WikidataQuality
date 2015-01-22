<?php

namespace WikidataQuality\ExternalValidation\UpdateTable\Importer;

use WikidataQuality\ExternalValidation\UpdateTable\ImportContext;


/**
 * Class Importer
 * @package WikidataQuality\ExternalValidation\UpdateTable\Importer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
abstract class Importer
{
    /**
     * Location of the dump for the current database
     * @var string
     */
    protected $dumpFile;

    /**
     * Data format of the dump for the current database
     * @var string
     */
    protected $dumpDataFormat;

    /**
     * Language of the dump for the current database
     * @var string
     */
    protected $dumpLanguage;

    /**
     * @var ImportContext
     */
    protected $importContext;


    /**
     * @param string $dumpFile
     * @param string $dumpDataFormat
     * @param string $dumpLanguage
     * @param \ImportContext $importContext
     */
    function __construct( $dumpFileName, $dumpDataFormat, $dumpLanguage, $importContext )
    {
        $this->dumpFile = __DIR__ . "/../../../dumps/$dumpFileName";
        $this->dumpDataFormat = $dumpDataFormat;
        $this->dumpLanguage = $dumpLanguage;
        $this->importContext = $importContext;
    }

    abstract function import();

    protected function deleteOldDatabaseEntries( $db, $propertyId )
    {
        global $wgDBtype;
        $tableName = $this->importContext->getTargetTableName();
        if ( !$db->tableExists( $tableName ) ) {
            if ( !$this->importContext->isQuiet() ) {
                print "$tableName table does not exist.\nExecuting core/maintenance/update.php may help.\n";
            }
        }
        if ( !$this->importContext->isQuiet() ) {
            print "Removing old entries\n";
        }
        if ( $wgDBtype === 'sqlite' ) {
            $db->delete( $tableName, "pid=" . $propertyId );
        } else {
            do {
                $db->commit( __METHOD__, 'flush' );
                wfWaitForSlaves();
                if ( !$this->importContext->isQuiet() ) {
                    print "Deleting a batch\n";
                }
                $table = $db->tableName( $tableName );
                $batchSize = $this->importContext->getBatchSize();
                $db->query( "DELETE FROM $table WHERE pid=$propertyId LIMIT $batchSize" );
            } while ( $db->affectedRows() > 0 );
        }
    }

    /**
     * Download dump of the current database
     * @param string $dumpUrl - url of the dump file
     */
    protected function downloadDump( $dumpUrl )
    {
        $targetFile = fopen( $this->dumpFile, "wb" );

        $curlSession = curl_init( $dumpUrl );
        curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curlSession, CURLOPT_FILE, $targetFile );
        if ( !$this->importContext->isQuiet() ) {
            curl_setopt( $curlSession, CURLOPT_NOPROGRESS, false );
            curl_setopt( $curlSession, CURLOPT_PROGRESSFUNCTION, array( $this, "downloadProgressCallback" ) );
        }
        curl_exec( $curlSession );
        /*$sourceFile = fopen( $dumpUrl, "rb" );
        $buffer = 1024 * 8;
        while( !feof( $sourceFile ) ) {
            fwrite( $targetFile, fread( $sourceFile, $buffer ), $buffer );
            if( !$this->importContext->isQuiet() ) {
                $this->downloadProgressCallback( null, null, ftell( $sourceFile ), null, null );
            }
        }
        if( !$this->importContext->isQuiet() ) {
            print "\n";
        }
        fclose( $sourceFile );*/

        if( !$this->importContext->isQuiet() ) {
            print "\n";
        }

        fclose( $targetFile );
    }

    /**
     * Progress callback function of downloading dumps
     * @param pointer $clientProgressData
     * @param double $downloadSize
     * @param double $downloadedSize
     * @param double $uploadSize
     * @param double $uploadedSize
     */
    private function downloadProgressCallback( $clientProgressData, $downloadTotal, $downloadNow, $uploadTotal, $uploadNow )
    {
        if ( empty( $downloadTotal ) ) {
            print "\r\033[K";
            print "Downloading database dump... " . $this->formatBytes( $downloadNow );
        } else {
            $progress = $downloadNow / $downloadTotal * 100;
            print "\r\033[K";
            print "Downloading database dump... $progress%";
        }
    }

    /**
     * Returns a string combined with a suitable unit
     * @param $bytes
     * @param int $precision
     * @return string
     */
    function formatBytes( $bytes, $precision = 2 )
    {
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB' );

        $bytes = max( $bytes, 0 );
        $pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
        $pow = min( $pow, count( $units ) - 1 );
        $bytes /= pow( 1024, $pow );

        return round( $bytes, $precision ) . ' ' . $units[ $pow ];
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
     * Mark database connection as available for reuse
     * @param \DatabaseBase $db
     */
    protected function reuseDbConnection( $db )
    {
        $loadBalancer = $this->importContext->getLoadBalancer();
        $loadBalancer->reuseConnection( $db );
    }

    /**
     * @param \DatabaseBase $db - database in which data object should be inserted
     * @param int $pid - property id of the external identifier
     * @param string $entityId - external identifier
     * @param string $entityData - external entity for the identifier
     */
    protected function insertEntity( $db, $pid, $entityId, $entityData )
    {
        $accumulator = array( "pid" => $pid, "entity_id" => $entityId, "entity_format" => $this->dumpDataFormat, "entity_data" => $entityData );

        $db->commit( __METHOD__, "flush" );
        wfWaitForSlaves();
        $db->insert( $this->importContext->getTargetTableName(), $accumulator );
    }
}