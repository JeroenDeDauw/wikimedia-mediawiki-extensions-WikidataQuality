#! /bin/bash

cd ../wiki/extensions/WikidataQuality

ls -lt build/logs

php vendor/bin/coveralls -v
