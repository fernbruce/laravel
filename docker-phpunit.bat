@echo off
docker exec laradock-workspace-1 php ./vendor/bin/phpunit %*
