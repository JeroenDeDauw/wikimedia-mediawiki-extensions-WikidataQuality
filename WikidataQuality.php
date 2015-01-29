<?php
# Alert the user that this is not a valid access point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
	To install my extension, put the following line in LocalSettings.php:
	require_once( "\$IP/extensions/WikidataQuality/WikidataQuality.php" );
EOT;
	exit( 1 );
}

// Enable autoload
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Set credits
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'WikidataQuality',
	'author' => 'BP2014N1',
	'url' => 'https://www.mediawiki.org/wiki/Extension:WikidataQuality',
	'descriptionmsg' => 'WikidataQuality-desc',
	'version' => '0.0.0',
);

// Initialize localization and aliases
$wgMessagesDirs['WikidataQuality'] = __DIR__ . "/i18n";
$wgExtensionMessagesFiles['WikidataQuality'] = __DIR__ . "/WikidataQuality.i18n.php";
$wgExtensionMessagesFiles['WikidataQualityAlias'] = __DIR__ . '/WikidataQuality.alias.php';

// Initalize hooks for creating database tables
global $wgHooks;
$wgHooks['LoadExtensionSchemaUpdates'][] = 'WikidataQualityHooks::onCreateSchema';

// Initialize special pages
$wgSpecialPages['ConstraintReport'] = 'WikidataQuality\ConstraintReport\Specials\SpecialWikidataConstraintReport';
$wgSpecialPages['CrossCheck'] = 'WikidataQuality\ExternalValidation\Specials\SpecialCrossCheck';

// Define modules
$wgResourceModules['SpecialCrossCheck'] = array(
	'styles' => 'external-validation/modules/SpecialCrossCheck.css',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'WikidataQuality'
);

// Define database table names
DEFINE("DUMP_DATA_TABLE", "wdq_external_data");
DEFINE("DUMP_META_TABLE", "wdq_dump_information");
