<?php

namespace WikidataQuality\ExternalValidation\CrossCheck;


/**
 * Class DumpMetaInformation
 * @package WikidataQuality\ExternalValidation\CrossCheck
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class DumpMetaInformation {
    /**
     * Data format of the dump.
     * @var string
     */
    private $format;

    /**
     * Language of data in the dump.
     * @var string
     */
    private $language;

    /**
     * Date format that is used in dump.
     * @var string
     */
    private $dateFormat;

    /**
     * Name of data source of the dump.
     * @var string
     */
    private $dataSourceName;


    /**
     * @param string $format
     * @param string $language
     * @param string $dateFormat
     * @param string $dataSourceName
     */
    public function __construct($format, $language, $dateFormat, $dataSourceName) {
        $this->format = $format;
        $this->language = $language;
        $this->dateFormat = $dateFormat;
        $this->dataSourceName = $dataSourceName;
    }


    /**
     * Returns data format.
     * @return string
     */
    public function getFormat() {
        return $this->format;
    }

    /**
     * Returns language.
     * @return string
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * Returns date format.
     * @return string
     */
    public function getDateFormat() {
        return $this->dateFormat;
    }

    /**
     * Returns data source name.
     * @return string
     */
    public function getDataSourceName() {
        return $this->dataSourceName;
    }
}