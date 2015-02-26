<?php

namespace WikidataQuality\ExternalValidation\UpdateTable\Importer;

use DateTime;
use SimpleXMLElement;
use WikidataQuality\ExternalValidation\CrossCheck\MappingEvaluator\MappingEvaluator;

class GndImporter extends Importer
{
    const DATABASE_NAME = 'GND';
    const DUMP_DATA_FORMAT = 'xml';
    const DUMP_LANGUAGE = 'de';
    const DUMP_URL_FORMAT = 'http://datendienst.dnb.de/cgi-bin/mabit.pl?cmd=fetch&userID=GNDxml&pass=gndmarcxml151&mabheft=Tpgesamt%d%sgndmrc.xml.gz';
    const DUMP_FILE_NAME = 'gnd.xml.gz';
    const DUMP_LICENSE = 'CC-0';
    const WD_PROPERTY_ID = '227';
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
    private $tempRecord = '';

    /**
     * Number of external entites that were imported
     * @var int
     */
    private $numberOfImportedEntites = 0;

    /**
     * Curretn database connection
     * @var \DatabaseBase
     */
    private $db;

    /**
     * Id of the current dump that is imported
     * @var int
     */
    private $dumpId;

    private $mapping;


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

        $mappingPath = implode( DIRECTORY_SEPARATOR, array( __DIR__, '..', '..', 'CrossCheck', 'mapping.inc.php' ) );
        require( $mappingPath );
        $this->mapping = $mapping;
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
/*        if ( !$this->downloadDump( $dumpUrl ) ) {
            // If download fails, try previous dump
            if ( !$this->importContext->isQuiet() ) {
                print "Download of latest dump failed. Try to download previous one...\n";
            }

            $dumpUrl = $this->buildDumpUrl( true );
            $this->downloadDump( $dumpUrl );
        }
*/
        // Connect to database and delete old entries
        $this->db = $this->establishDbConnection();

        // Insert meta information
        $dumpSize = filesize( $this->dumpFile );
        $this->insertMetaInformation( $this->db, self::DATABASE_NAME, self::DUMP_DATA_FORMAT, self::DUMP_LANGUAGE, $dumpUrl, $dumpSize, self::DUMP_LICENSE );
        $this->dumpId = $this->getDumpId( $this->db, self::DATABASE_NAME );

        // Delete old entries
        $this->deleteOldDatabaseEntries( $this->db, self::WD_PROPERTY_ID );

        // Parse dump and insert entities
        xml_set_element_handler(
            $this->parser,
            'startElement',
            'endElement'
        );
        xml_set_character_data_handler( $this->parser, 'characterData' );
        $this->parseDump( $this->db );

        // Release connection
        $this->reuseDbConnection( $this->db );
        $this->db = null;
    }

    /**
     * Builds url of the latest dump
     * @param $previous - If true, url of the previous dump will be returned
     * @return string - url of the dump
     */
    private function buildDumpUrl( $previous = false )
    {
        $now = new DateTime();
        $year = intval( $now->format( 'y' ) );
        $month = intval( $now->format( 'm' ) );

        if ( $previous ) {
            if ( $month === 1 ) {
                $month = '06';
                $year--;
            } elseif ( $month < 6 ) {
                $month = '10';
                $year--;
            } elseif ( $month < 10 ) {
                $month = '02';
            } else {
                $month = '06';
            }
        } else {
            if ( $month === 1 ) {
                $month = '10';
                $year--;
            } elseif ( $month < 6 ) {
                $month = '02';
            } elseif ( $month < 10 ) {
                $month = '06';
            } else {
                $month = '10';
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
            print 'Importing entities... ';
        }

        $file = gzopen( $this->dumpFile, 'rb' );
        while ( $data = gzread( $file, self::BUFFER_SIZE ) ) {
            xml_parse( $this->parser, $data, feof( $file ) );
        }
        gzclose( $file );
    }

    /**
     * SAX callback function for start-element event
     * @param string $name - name of the starting element
     * @param Array $attributes - attributes of the starting element
     */
    private function startElement( $name, $attributes )
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
     * @param string $name - name of the ending element
     */
    private function endElement( $name )
    {
        $this->tempRecord .= "</$name>";
        if ( $name == "RECORD" ) {
            $externalEntity = $this->tempRecord;
            $mappingEvaluator = MappingEvaluator::getEvaluator( self::DUMP_DATA_FORMAT, $externalEntity );
            $mapping = $this->mapping[ self::WD_PROPERTY_ID ];

            if ( $mappingEvaluator && $mapping ) {
                foreach ( $mapping as $pid => $propertyMapping ){
                    $externalValues = $this->evaluatePropertyMapping( $mappingEvaluator, $propertyMapping );
                    foreach ($externalValues as $externalValue) {
                        $this->insertEntity($this->db, $this->dumpId, self::WD_PROPERTY_ID, $this->getEntityId($this->tempRecord), $pid, $externalValue);
                    }
                }
            }

            $this->numberOfImportedEntites++;
            if ( !$this->importContext->isQuiet() ) {
                print "\r\033[K";
                print 'Importing entities... ' . $this->numberOfImportedEntites;
            }
            $this->tempRecord = "";
        }
    }

    /**
     * SAX callback function for character-data event
     * @param string $cdata - content of an element
     */
    private function characterData( $cdata )
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

    private function evaluatePropertyMapping( $mappingEvaluator, $propertyMapping )
    {
        $nodeSelector = $propertyMapping[ 'nodeSelector' ];
        $valueFormatter = array_key_exists( 'valueFormatter', $propertyMapping ) ? $propertyMapping[ 'valueFormatter' ] : null;
        $externalValues = $mappingEvaluator->evaluate( $nodeSelector, $valueFormatter );
        return $externalValues;
    }


}