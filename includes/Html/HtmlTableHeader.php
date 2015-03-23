<?php

namespace WikidataQuality\Html;


/**
 * Class HtmlTableHeader
 * @package WikidataQuality\Html
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class HtmlTableHeader {
    /**
     * Text of the header
     * @var string
     */
    private $text;

    /**
     * Determines, whether the column should be sortable or not.
     * @var bool
     */
    private $isSortable;


    /**
     * @param string $text
     * @param bool $isSortable
     */
    public function __construct( $text, $isSortable = false )
    {
        $this->text = $text;
        $this->isSortable = $isSortable;
    }


    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return bool
     */
    public function getIsSortable()
    {
        return $this->isSortable;
    }
}