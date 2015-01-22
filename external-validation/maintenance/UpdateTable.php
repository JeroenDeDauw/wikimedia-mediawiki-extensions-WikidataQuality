<?php

namespace WikidataQuality\ExternalValidation\Maintenance;

use Maintenance;
use LoadBalancer;
use WikidataQuality\ExternalValidation\UpdateTable\ImportContext;


$basePath = getenv( "MW_INSTALL_PATH" ) !== false ? getenv( "MW_INSTALL_PATH" ) : __DIR__ . "/../../../..";
require_once $basePath . "/maintenance/Maintenance.php";


class UpdateTable extends Maintenance
{
    /**
     * Name of the database table used for external data
     */
    const TABLE_NAME = "wbq_external_data";

    /**
     * @var array - array of importers to run
     */
    private $importers = array( 'WikidataQuality\ExternalValidation\UpdateTable\Importer\GndImporter' );


    function __construct()
    {
        parent::__construct();
        $this->mDescription = "Downloads dumps of external databases and imports the entities into the local database.";
        $this->setBatchSize( 1000 );
    }


    function  execute()
    {
        // Get load balancer
        wfWaitForSlaves();
        $loadBalancer = wfGetLB();

        // Run each selected importer
        $context = new ImportContext( $loadBalancer, self::TABLE_NAME, $this->mBatchSize, $this->isQuiet() );
        foreach ( $this->importers as $class ) {
            $namespaceExp = explode( '\\', $class );
            $className = array_pop( $namespaceExp );
            $this->output( "Running $className...\n" );

            $importer = new $class( $context );
            $importer->import();

            $this->output( "\n" );
        }
    }
}


$maintClass = 'WikidataQuality\ExternalValidation\Maintenance\UpdateTable';
require_once RUN_MAINTENANCE_IF_MAIN;