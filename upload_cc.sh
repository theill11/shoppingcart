#!/bin/bash
set -ev
# upload code coverage after travis build only when version
if [ "${TRAVIS_PHP_VERSION}" = "5.6" ]; then
    wget https://scrutinizer-ci.com/ocular.phar
    php ocular.phar code-coverage:upload --format=php-clover build/logs/clover.xml
    CODECLIMATE_REPO_TOKEN="2a816e25a4bcd8ded5ca51664e7f432c134d024ec16b76309dfb18665b0f9c9a" vendor/bin/test-reporter --stdout > codeclimate.json
    "curl -X POST -d @codeclimate.json -H 'Content-Type: application/json' -H 'User-Agent: Code Climate (PHP Test Reporter v0.1.1)' https://codeclimate.com/test_reports"
fi
