<?php
# Alert the user that this is not a valid access point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
	To install my extension, put the following line in LocalSettings.php:
	require_once( "\$IP/extensions/WikidataQuality/WikidataQuality.php" );
EOT;
	exit( 1 );
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}
 
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'WikidataQuality',
	'author' => 'BP2014N1',
	'url' => 'https://www.mediawiki.org/wiki/Extension:WikidataQuality',
	'descriptionmsg' => 'WikidataQuality-desc',
	'version' => '0.0.0',
);
 
$wgMessagesDirs['WikidataQuality'] = __DIR__ . "/i18n"; # Location of localization files (Tell MediaWiki to load them)
$wgExtensionMessagesFiles['WikidataQualityAlias'] = __DIR__ . '/WikidataQuality.alias.php'; # Location of an aliases file (Tell MediaWiki to load it)

$wgAutoloadClasses['SpecialWikidataConstraintReport'] = __DIR__ . '/constraint-report/special/SpecialWikidataConstraintReport.php'; # Location of the SpecialWikidataConstraintReport class (Tell MediaWiki to load this file)
$wgSpecialPages['WikidataConstraintReport'] = 'SpecialWikidataConstraintReport'; # Tell MediaWiki about the new special page and its class name
$wgSpecialPageGroups['WikidataConstraintReport'] = 'Wikidata';
global $wgHooks;
$wgHooks['LoadExtensionSchemaUpdates'][] = 'WikidataQualityHooks::onCreateSchema';