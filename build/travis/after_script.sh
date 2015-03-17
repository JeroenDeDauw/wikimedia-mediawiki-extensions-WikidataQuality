#! /bin/bash

cd ../wiki/extensions/WikidataQuality

ls build/logs

php vendor/bin/coveralls -v
