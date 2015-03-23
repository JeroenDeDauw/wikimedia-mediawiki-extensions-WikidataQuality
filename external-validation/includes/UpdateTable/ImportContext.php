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
     * Path of the entities file to be imported.
     * @var string
     */
    private $entitiesFilePath;

    /**
     * Path of the meta information file to be imported.
     * @var string
     */
    private $metaInformationFilePath;

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
    function __construct( $loadBalancer, $targetTableName, $batchSize, $quiet, $entitiesFilePath, $metaInformationFilePath )
    {
        $this->setLoadBalancer( $loadBalancer );
        $this->setTargetTableName( $targetTableName );
        $this->setBatchSize( $batchSize );
        $this->setQuiet( $quiet );
        $this->setEntitiesFilePath( $entitiesFilePath );
        $this->setMetaInformationFilePath( $metaInformationFilePath );
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
     * @return string
     */
    public function getEntitiesFilePath( )
    {
        return $this->entitiesFilePath;
    }

    /**
     * @return string
     */
    public function getMetaInformationFilePath( )
    {
        return $this->metaInformationFilePath;
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

    /**
     * @param string $entitiesFilePath
     */
    public function setEntitiesFilePath( $entitiesFilePath )
    {
        $this->entitiesFilePath = $entitiesFilePath;
    }

    /**
     * @param string $metaInformationFilePath
     */
    public function setMetaInformationFilePath( $metaInformationFilePath )
    {
        $this->metaInformationFilePath = $metaInformationFilePath;
    }
}