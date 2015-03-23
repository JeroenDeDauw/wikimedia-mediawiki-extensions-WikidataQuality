<?php

namespace WikidataQuality\ExternalValidation\Maintenance;

use Maintenance;
use WikidataQuality\ExternalValidation\UpdateTable\ImportContext;
use WikidataQuality\ExternalValidation\UpdateTable\Importer;


// @codeCoverageIgnoreStart
$basePath = getenv( "MW_INSTALL_PATH" ) !== false ? getenv( "MW_INSTALL_PATH" ) : __DIR__ . "/../../../..";
require_once $basePath . "/maintenance/Maintenance.php";
// @codeCoverageIgnoreEnd


class UpdateTable extends Maintenance
{
    function __construct()
    {
        parent::__construct();
        $this->mDescription = "Downloads dumps of external databases and imports the entities into the local database.";
        $this->addOption( 'entities-file', 'CSV file that contains external entities.', true, true );
        $this->addOption( 'meta-information-file', 'CSV file that contains meta information about the data source.', true, true );
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
            $this->mBatchSize,
            $this->isQuiet(),
            $this->getOption( 'entities-file' ),
            $this->getOption( 'meta-information-file' )
        );
        $importer = new Importer( $context );
        $importer->import();
    }
}


// @codeCoverageIgnoreStart
$maintClass = 'WikidataQuality\ExternalValidation\Maintenance\UpdateTable';
require_once RUN_MAINTENANCE_IF_MAIN;
// @codeCoverageIgnoreEnd