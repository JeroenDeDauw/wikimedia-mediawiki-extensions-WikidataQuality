<?php

namespace WikidataQuality\ExternalValidation\Maintenance;

use Maintenance;
use WikidataQuality\ExternalValidation\UpdateTable\ImportContext;
use WikidataQuality\ExternalValidation\UpdateTable\Importer\Importer;


$basePath = getenv( "MW_INSTALL_PATH" ) !== false ? getenv( "MW_INSTALL_PATH" ) : __DIR__ . "/../../../..";
require_once $basePath . "/maintenance/Maintenance.php";


class UpdateTable extends Maintenance
{
    function __construct()
    {
        parent::__construct();
        $this->mDescription = "Downloads dumps of external databases and imports the entities into the local database.";
        $this->addOption( 'entitiesFile', 'CSV file that contains external entities.', true, true );
        $this->addOption( 'metaFile', 'CSV file that contains meta information about the data source.', true, true );
        $this->setBatchSize( 1000 );
    }


    function  execute()
    {
        // Get load balancer
        wfWaitForSlaves();
        $loadBalancer = wfGetLB();

        // Run importer
        $context = new ImportContext(
            $loadBalancer,
            DUMP_DATA_TABLE,
            DUMP_META_TABLE,
            $this->mBatchSize,
            $this->isQuiet(),
            $this->getOption( 'entitiesFile' ),
            $this->getOption( 'metaFile' )
        );
        $importer = new Importer( $context );
        $importer->import();
    }
}


$maintClass = 'WikidataQuality\ExternalValidation\Maintenance\UpdateTable';
require_once RUN_MAINTENANCE_IF_MAIN;