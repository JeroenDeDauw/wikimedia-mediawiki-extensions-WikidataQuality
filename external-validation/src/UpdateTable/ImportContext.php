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
    private $targetTableName = '';

    /**
     * table name of the table with dump meta information
     * @var string
     */
    private $metaTableName = '';

    /**
     * @var LoadBalancer
     */
    private $loadBalancer;

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
     * @param string $targetTableName
     * @param string $metaTableName
     * @param int $batchSize
     * @param boolean $quiet
     */
    function __construct( $loadBalancer, $targetTableName, $metaTableName, $batchSize, $quiet )
    {
        $this->setLoadBalancer( $loadBalancer );
        $this->setTargetTableName( $targetTableName );
        $this->setMetaTableName( $metaTableName );
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
     * @return string
     */
    public function getMetaTableName()
    {
        return $this->metaTableName;
    }

    /**
     * @param string $tableName
     */
    public function setMetaTableName( $tableName )
    {
        $this->metaTableName = $tableName;
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