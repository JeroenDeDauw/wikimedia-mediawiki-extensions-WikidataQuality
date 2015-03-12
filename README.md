# WikidataQuality  [![Build Status](https://travis-ci.org/WikidataQuality/wikimedia-mediawiki-extensions-WikidataQuality.svg)](https://travis-ci.org/WikidataQuality/wikimedia-mediawiki-extensions-WikidataQuality)

## Installation

* Clone this repo into Wikidata/extensions

`git clone https://github.com/WikidataQuality/wikimedia-mediawiki-extensions-WikidataQuality.git WikidataQuality`  

* Add `require_once __DIR__ . "/extensions/Wikidata/extensions/WikidataQuality/WikidataQuality.php";` to your LocalSettings.php
* Run `php maintenance/update.php --quick` in your Mediawiki directory
* Run `composer install` in the WikidataQuality directory
