#! /bin/bash

set -x

cd ../wiki/tests/phpunit
php phpunit.php -c ../../extensions/WikidataQuality/phpunit.xml.dist

# cd ../wiki/extensions/WikidataQuality
# php vendor/bin/phpunit -c phpunit.xml.dist