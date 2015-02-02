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
        $this->dumpFile = join( DIRECTORY_SEPARATOR, array( __DIR__, "..", "..", "..", "dumps", $dumpFileName ) );
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
        // Create directory, if needed
        $dirName = dirname( $this->dumpFile );
        if ( !is_dir( $dirName ) ) {
            mkdir( $dirName );
        }

        // Create file
        $targetFile = fopen( $this->dumpFile, "wb" );

        // Start curl for downloading
        $curlSession = curl_init( $dumpUrl );
        curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curlSession, CURLOPT_FILE, $targetFile );
        if ( !$this->importContext->isQuiet() ) {
            curl_setopt( $curlSession, CURLOPT_NOPROGRESS, false );
            curl_setopt( $curlSession, CURLOPT_PROGRESSFUNCTION, array( $this, "downloadProgressCallback" ) );
        }
        curl_exec( $curlSession );

        //Check for errors
        if ( !curl_errno( $curlSession ) ) {
            return false;
        }

        if ( !$this->importContext->isQuiet() ) {
            print "\n";
        }

        fclose( $targetFile );

        return true;
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
     * Inserts given meta information of the current dump
     * @param \DatabaseBase $db
     * @param string $name
     * @param string $format
     * @param string $language
     * @param string $source
     * @param int $size
     * @param string $license
     */
    protected function insertMetaInformation( $db, $name, $format, $language, $source, $size, $license )
    {
        $accumulator = array(
            "name" => $name,
            "date" => null,
            "format" => $format,
            "language" => $language,
            "source" => $source,
            "size" => $size,
            "license" => $license
        );

        $db->commit( __METHOD__, "flush" );
        wfWaitForSlaves();

        $rowCount = $db->selectRowCount( $this->importContext->getMetaTableName(), "*", "name=\"$name\"" );
        if ( $rowCount == 0 ) {
            $db->insert( $this->importContext->getMetaTableName(), $accumulator );
        } else {
            $db->update( $this->importContext->getMetaTableName(), $accumulator, array( "name=\"$name\"" ) );
        }
    }

    /**
     * Get id of the dump
     * @param \DatabaseBase $db
     * @param string $name
     * @return bool
     */
    protected function getDumpId( $db, $name )
    {
        $result = $db->selectRow( $this->importContext->getMetaTableName(), "row_id", "name=\"$name\"" );
        if ( $result == false ) {
            return false;
        } else {
            return $result->row_id;
        }
    }

    /**
     * @param \DatabaseBase $db - database in which data object should be inserted
     * @param string $dumpId - dump id
     * @param int $pid - property id of the external identifier
     * @param string $externalId - external identifier
     * @param string $externalData - external entity for the identifier
     */
    protected function insertEntity( $db, $dumpId, $pid, $externalId, $externalData )
    {
        $accumulator = array(
            "dump_id" => $dumpId,
            "pid" => $pid,
            "external_id" => $externalId,
            "external_data" => $externalData
        );

        $db->commit( __METHOD__, "flush" );
        wfWaitForSlaves();
        $db->insert( $this->importContext->getTargetTableName(), $accumulator );
    }
}