<?php

namespace WikidataQuality\Html;


use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Html;


/**
 * Class HtmlTable
 * @package WikidataQuality\Html
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class HtmlTable {
    /**
     * Headers of the table.
     * @var HtmlTableHeader
     */
    private $headers = array();

    /**
     * Rows of the table.
     * @var array
     */
    private $rows = array();

    /**
     * Number of columns of the table.
     * @var int
     */
    private $columnsCount;

    /**
     * Determines, whether the table is sortable.
     * @var bool
     */
    private $isSortable = false;


    /**
     * @param array $headers
     * @param bool $isSortable
     */
    public function __construct( $headers, $isSortable = false )
    {
        if( is_array( $headers ) )
        {
            foreach ( $headers as $header ) {
                if( is_string( $header ) )
                {
                    $this->headers[] = new HtmlTableHeader( $header );
                }
                elseif ( $header instanceof HtmlTableHeader )
                {
                    $this->headers[] = $header;

                    if ( $header->getIsSortable() )
                    {
                        $this->isSortable = true;
                    }
                }
                else
                {
                    throw new InvalidArgumentException('$headers must be an array of strings or HtmlTableHeader elements.');
                }
            }
        }
        else
        {
            throw new InvalidArgumentException('$headers must be an array of strings or HtmlTableHeader elements.');
        }

        $this->columnsCount = count($headers);
    }


    /**
     * @return HtmlTableHeader
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return bool
     */
    public function getIsSortable()
    {
        return $this->isSortable;
    }


    /**
     * Adds row with specified cells to table.
     * @param $cells
     */
    public function appendRow( $cells )
    {
        // Check cells
        if( !is_array( $cells ) )
        {
            throw new InvalidArgumentException('$cells must be array of strings.');
        }
        if( count( $cells ) != $this->columnsCount )
        {
            throw new InvalidArgumentException('$cells must contain ' . $this->columnsCount . ' cells.');
        }
        foreach ( $cells as $cell ) {
            if( !is_string( $cell ))
            {
                throw new InvalidArgumentException('$cells must be array of strings.');
            }
        }

        // Add cells into new row
        $this->rows[] = $cells;
    }

    /**
     * Returns table as html.
     * @return string
     */
    public function toHtml()
    {
        // Open table
        $tableClasses = 'wikitable';
        if( $this->isSortable )
        {
            $tableClasses .= ' sortable jquery-tablesort';
        }
        $html = Html::openElement(
            'table',
            array(
                'class' => $tableClasses
            )
        );

        // Write headers
        $html .= Html::openElement( 'tr' );
        foreach ( $this->headers as $header )
        {
            $html .= Html::openElement(
                'th',
                array(
                    'role' => 'columnheader button',
                    'class' => $this->isSortable && !$header->getIsSortable() ? 'unsortable' : ''
                )
            )
            . $header->getText()
            . Html::closeElement( 'th' );
        }
        $html .= Html::closeElement( 'tr' );

        // Write rows
        foreach( $this->rows as $row )
        {
            $html .= Html::openElement( 'tr' );
            foreach ( $row as $cell )
            {
                $html .= Html::openElement( 'td' )
                    . $cell
                    . Html::closeElement( 'td' );
            }
            $html .= Html::closeElement( 'tr' );
        }

        // Close table
        $html .= Html::closeElement( 'table' );

        return $html;
    }
}