<?php

namespace WikidataQuality\Tests\Helper;


use Wikibase\Repo\WikibaseRepo;
use Wikibase\Lib\Store\EntityLookup;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\DeserializerFactory;
use DataValues\Deserializers\DataValueDeserializer;


class JsonFileEntityLookup implements EntityLookup {
    /**
     * Base dir which contains serialized entities as json files.
     * @var string
     */
    private $baseDir;


    /**
     * @param string $baseDir
     */
    public function __construct( $baseDir = __DIR__ ) {
        $this->baseDir = $baseDir;
    }


    /**
     * Returns the entity with the provided id or null if there is no such entity.
     * @param EntityId $entityId
     */
    public function getEntity( EntityId $entityId )
    {
        if( $this->hasEntity( $entityId ) ) {
            $filePath = $this->buildFilePath( $entityId );
            $serializedEntity = json_decode( file_get_contents( $filePath ), true );
            //fwrite(STDERR, "\n" . print_r($serializedEntity, TRUE));
            if ( $serializedEntity ) {
                $deserializerFactory = new DeserializerFactory(
                    new DataValueDeserializer(
                        array(
                            'boolean' => 'DataValues\BooleanValue',
                            'number' => 'DataValues\NumberValue',
                            'string' => 'DataValues\StringValue',
                            'unknown' => 'DataValues\UnknownValue',
                            'globecoordinate' => 'DataValues\GlobeCoordinateValue',
                            'monolingualtext' => 'DataValues\MonolingualTextValue',
                            'multilingualtext' => 'DataValues\MultilingualTextValue',
                            'quantity' => 'DataValues\QuantityValue',
                            'time' => 'DataValues\TimeValue',
                            'wikibase-entityid' => 'Wikibase\DataModel\Entity\EntityIdValue',
                        )
                    ),
                    WikibaseRepo::getDefaultInstance()->getEntityIdParser()
                );
                return $deserializerFactory->newEntityDeserializer()->deserialize( $serializedEntity );
            }
        }
    }

    /**
     * Returns whether the given entity can bee looked up using getEntity().
     * @param EntityId $entityId
     */
    public function hasEntity( EntityId $entityId )
    {
        return file_exists( $this->buildFilePath( $entityId ) );
    }


    /**
     * Returns path of the file, which contains the serialized entity.
     * @param EntityId $entityId
     * @return string
     */
    private function buildFilePath( EntityId $entityId ) {
        $filePath = sprintf('%s\\%s.json', $this->baseDir, (string)$entityId);
        //fwrite(STDERR, "\n" . print_r($filePath, TRUE));
        return $filePath;
    }
}