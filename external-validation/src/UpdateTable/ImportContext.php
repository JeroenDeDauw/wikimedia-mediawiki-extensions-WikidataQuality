<?php

namespace WikidataQuality\ExternalValidation\UpdateTable;

use LoadBalancer;


/**
 * Context for importing data from a csv file to a db table using a Importer strategy
 *
 * @package WikidataQuality\ExternalValidation\UpdateTable
 * @author BP2014N1
 * @licence GNU GPL v2+
 */
class ImportContext
{
    /**
     * table name of the table to import to
     * @var string
     */
    private $targetTableName = "";

    /**
     * @var LoadBalancer
     */
    private $loadBalancer = null;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var boolean
     */
    private $quiet;

    /**
     * @param LoadBalancer $loadBalancer
     * @param string $tableName
     * @param int $batchSize
     * @param boolean $quiet
     */
    function __construct( $loadBalancer, $tableName, $batchSize, $quiet )
    {
        $this->setLoadBalancer( $loadBalancer );
        $this->setTargetTableName( $tableName );
        $this->setBatchSize( $batchSize );
        $this->setQuiet( $quiet );
    }

    /**
     * @return LoadBalancer
     */
    public function getLoadBalancer()
    {
        return $this->loadBalancer;
    }

    /**
     * @param LoadBalancer $loadBalancer
     */
    public function setLoadBalancer( $loadBalancer )
    {
        $this->loadBalancer = $loadBalancer;
    }

    /**
     * @return string
     */
    public function getTargetTableName()
    {
        return $this->targetTableName;
    }

    /**
     * @param string $tableName
     */
    public function setTargetTableName( $tableName )
    {
        $this->targetTableName = $tableName;
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        return $this->batchSize;
    }

    /**
     * @param int $batchSize
     */
    public function setBatchSize( $batchSize )
    {
        $this->batchSize = $batchSize;
    }

    /**
     * @return boolean
     */
    public function isQuiet()
    {
        return $this->quiet;
    }

    /**
     * @param boolean $quiet
     */
    public function setQuiet( $quiet )
    {
        $this->quiet = $quiet;
    }
}