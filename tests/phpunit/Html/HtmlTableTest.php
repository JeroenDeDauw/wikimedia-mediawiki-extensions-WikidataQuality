<?php

namespace WikidataQuality\ExternalValidation\Tests\Html;


use WikidataQuality\Html\HtmlTable;
use WikidataQuality\Html\HtmlTableHeader;


/**
 * @covers WikidataQuality\Html\HtmlTable
 * @covers WikidataQuality\Html\HtmlTableHeader
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class HtmlTableTest extends \MediaWikiTestCase {
    /**
     * @dataProvider constructDataProvider
     */
    public function testConstruct( $headers, $expectedHeaders, $expectedIsSortable, $expectedException )
    {
        $this->setExpectedException( $expectedException );
        $htmlTable = new HtmlTable( $headers );

        $this->assertArrayEquals( $expectedHeaders, $htmlTable->getHeaders() );
        $this->assertEquals( $expectedIsSortable, $htmlTable->getIsSortable() );
    }

    /**
     * @return array
     */
    public function constructDataProvider()
    {
        return array(
            array(
                array(
                    'foo',
                    'bar'
                ),
                array(
                    new HtmlTableHeader( 'foo' ),
                    new HtmlTableHeader( 'bar' )
                ),
                false,
                null
            ),
            array(
                array(
                    new HtmlTableHeader( 'foo', true ),
                    new HtmlTableHeader( 'bar' )
                ),
                array(
                    new HtmlTableHeader( 'foo', true ),
                    new HtmlTableHeader( 'bar' )
                ),
                true,
                null
            ),
            array(
                array( 42 ),
                null,
                false,
                'InvalidArgumentException'
            ),
            array(
                'foobar',
                null,
                false,
                'InvalidArgumentException'
            )
        );
    }


    /**
     * @dataProvider appendRowDataProvider
     */
    public function testAppendRow( $row, $expectedRows, $expectedException )
    {
        $this->setExpectedException( $expectedException );
        $htmlTable = new HtmlTable(
            array(
                'fu',
                'bar'
            )
        );
        $htmlTable->appendRow( $row );

        $this->assertArrayEquals( $expectedRows, $htmlTable->getRows() );
    }

    /**
     * @return array
     */
    public function appendRowDataProvider()
    {
        return array(
            array(
                array(
                    'fucked up',
                    'beyond all recognition'
                ),
                array(
                    array(
                        'fucked up',
                        'beyond all recognition'
                    )
                ),
                null
            ),
            array(
                'foobar',
                null,
                'InvalidArgumentException'
            ),
            array(
                array(
                    42,
                    42
                ),
                null,
                'InvalidArgumentException'
            ),
            array(
                array(
                    'foobar'
                ),
                null,
                'InvalidArgumentException'
            )
        );
    }


    /**
     * @dataProvider toHtmlDataProvider
     */
    public function testToHtml( $headers, $rows, $expectedHtml )
    {
        //Create table
        $htmlTable = new HtmlTable( $headers );
        foreach ( $rows as $row ) {
            $htmlTable->appendRow( $row );
        }

        // Run assertions
        $actualHtml = $htmlTable->toHtml();
        $this->assertEquals( $expectedHtml, $actualHtml );
    }

    /**
     * @return array
     */
    public function toHtmlDataProvider()
    {
        return array(
            array(
                array(
                    new HtmlTableHeader( 'fu' ),
                    new HtmlTableHeader( 'bar' )
                ),
                array(
                    array(
                        'fucked up',
                        'beyond all recognition'
                    )
                ),
                '<table class="wikitable"><tr><th role="columnheader button">fu</th><th role="columnheader button">bar</th></tr><tr><td>fucked up</td><td>beyond all recognition</td></tr></table>'
            ),
            array(
                array(
                    new HtmlTableHeader( 'fu' ),
                    new HtmlTableHeader( 'bar', true )
                ),
                array(
                    array(
                        'fucked up',
                        'beyond all recognition'
                    )
                ),
                '<table class="wikitable sortable jquery-tablesort"><tr><th role="columnheader button" class="unsortable">fu</th><th role="columnheader button">bar</th></tr><tr><td>fucked up</td><td>beyond all recognition</td></tr></table>'
            )
        );
    }
}
