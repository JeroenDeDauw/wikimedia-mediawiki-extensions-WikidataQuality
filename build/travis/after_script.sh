#! /bin/bash

cd ../wiki/extensions/WikidataQuality

ls -lt build/logs

cat build/logs/clover.xml

php vendor/bin/coveralls -v
