<?php

namespace WikidataQuality\ExternalValidation\UpdateTable\Importer;

use DateTime;
use SimpleXMLElement;

class GndImporter extends Importer
{
    const DATABASE_NAME = "GND";
    const DUMP_DATA_FORMAT = "xml";
    const DUMP_LANGUAGE = "de";
    const DUMP_URL_FORMAT = "http://datendienst.dnb.de/cgi-bin/mabit.pl?cmd=fetch&userID=GNDxml&pass=gndmarcxml143&mabheft=Tpgesamt%d%dgndmrc.xml.gz";
    const DUMP_LICENSE = "CC-0 1.0";
    const DUMP_FILE_NAME = "gnd.xml.gz";
    const WD_PROPERTY_ID = "227";
    const ENTITY_ID_XPATH = "/RECORD/CONTROLFIELD[@TAG=\"001\"]/text()";
    const BUFFER_SIZE = 4096;

    /**
     * XML parse to read dump
     * @var Xml parser
     */
    private $parser;

    /**
     * Temporary record that is used when parsing the xml dump
     * @var string
     */
    private $tempRecord = "";

    /**
     * Number of external entites that were imported
     * @var int
     */
    private $numberOfImportedEntites = 0;


    /**
     * @param \ImportContext $importContext
     */
    function __construct( $importContext )
    {
        parent::__construct( self::DUMP_FILE_NAME, self::DUMP_DATA_FORMAT, self::DUMP_LANGUAGE, $importContext );

        // Create XML SAX parser
        $this->parser = xml_parser_create();
        xml_parser_set_option( $this->parser, XML_OPTION_CASE_FOLDING, true );
        xml_parser_set_option( $this->parser, XML_OPTION_SKIP_WHITE, true );
        xml_set_object( $this->parser, $this );
    }

    /**
     * Free all resources
     */
    function __destruct()
    {
        xml_parser_free( $this->parser );
    }


    /**
     * Download and import latest database dump
     */
    function import()
    {
        // Download dump
        $dumpUrl = $this->buildDumpUrl();
        if ( !$this->downloadDump( $dumpUrl ) ) {
            // If download fails, try previous dump
            $dumpUrl = $this->buildDumpUrl( true );
            $this->downloadDump( $dumpUrl );
        }

        // Connect to database and delete old entries
        $db = $this->establishDbConnection();

        // Insert meta information
        $dumpSize = filesize( $this->dumpFile );
        $this->insertMetaInformation( $db, self::DATABASE_NAME, self::DUMP_DATA_FORMAT, self::DUMP_LANGUAGE, $dumpUrl, $dumpSize, self::DUMP_LICENSE );
        $dumpId = $this->getDumpId( $db, self::DATABASE_NAME );

        // Delete old entries
        $this->deleteOldDatabaseEntries( $db, self::WD_PROPERTY_ID );

        // Parse dump and insert entities
        xml_set_element_handler(
            $this->parser,
            "startElement",
            function ( $parser, $name ) use ( $db, $dumpId ) {
                $this->endElement( $db, $dumpId, $name );
            }
        );
        xml_set_character_data_handler( $this->parser, "characterData" );
        $this->parseDump( $db );

        // Release connection
        $this->reuseDbConnection( $db );
    }

    /**
     * Builds url of the latest dump
     * @param $previous - If true, url of the previous dump will be returned
     * @return string - url of the dump
     */
    private function buildDumpUrl( $previous = false )
    {
        $now = new DateTime();
        $year = intval( $now->format( "y" ) );
        $month = intval( $now->format( "m" ) );

        if ( $previous ) {
            if ( $month == 1 ) {
                $month = 6;
                $year--;
            } else if ( $month < 6 ) {
                $month = 10;
                $year--;
            } else if ( $month < 10 ) {
                $month = 2;
            } else {
                $month = 6;
            }
        } else {
            if ( $month == 1 ) {
                $month = 10;
                $year--;
            } else if ( $month < 6 ) {
                $month = 2;
            } else if ( $month < 10 ) {
                $month = 6;
            } else {
                $month = 10;
            }
        }
        $url = sprintf( self::DUMP_URL_FORMAT, $year, $month );

        return $url;
    }

    /**
     * Parse database dump and add single data objects
     * @param \DatabaseBase $db - database in which data object should be inserted
     */
    private function parseDump( $db )
    {
        if ( !$this->importContext->isQuiet() ) {
            print "Importing entities... ";
        }

        $file = gzopen( $this->dumpFile, 'rb' );
        while ( $data = gzread( $file, self::BUFFER_SIZE ) ) {
            xml_parse( $this->parser, $data, feof( $file ) );
        }
        gzclose( $file );
    }

    /**
     * SAX callback function for start-element event
     * @param Xml parser $parser - current xml parser
     * @param string $name - name of the starting element
     * @param Array $attributes - attributes of the starting element
     */
    private function startElement( $parser, $name, $attributes )
    {
        if ( $name == "RECORD" || !empty( $this->tempRecord ) ) {
            $this->tempRecord .= "<$name";
            foreach ( array_keys( $attributes ) as $key ) {
                $this->tempRecord .= " $key=\"$attributes[$key]\"";
            }
            $this->tempRecord .= ">";
        }
    }

    /**
     * SAX callback function for end-element event
     * @param \DatabaseBase $db - database connection, that should be used to insert element
     * @param string $name - name of the ending element
     */
    private function endElement( $db, $dumpId, $name )
    {
        $this->tempRecord .= "</$name>";
        if ( $name == "RECORD" ) {
            $this->insertEntity( $db, $dumpId, self::WD_PROPERTY_ID, $this->getEntityId( $this->tempRecord ), $this->tempRecord );
            $this->numberOfImportedEntites++;
            if ( !$this->importContext->isQuiet() ) {
                print "\r\033[K";
                print "Importing entities... " . $this->numberOfImportedEntites;
            }
            $this->tempRecord = "";
        }
    }

    /**
     * SAX callback function for character-data event
     * @param Xml parser $parser - current xml parser
     * @param string $cdata - content of an element
     */
    private function characterData( $parser, $cdata )
    {
        $this->tempRecord .= htmlspecialchars( $cdata );
    }

    /**
     * Extracts the id of an external entity
     * @param string $entity - the data object from which the id should be determined
     * @return int - id of the given data object
     */
    private function getEntityId( $entity )
    {
        $xml = new SimpleXMLElement( $entity );
        $ext_id = $xml->xpath( self::ENTITY_ID_XPATH )[ 0 ];

        return $ext_id;
    }
}