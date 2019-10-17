SQITCH_VERSION ?= $(shell bash -c 'read -p "Version: " version; echo $$version')
SQITCH_MESSAGE ?= $(shell bash -c 'read -p "Message: " message; echo $$message')
CONTAINER_NAME ?= $(shell bash -c 'read -p "Container Name: " container; echo $$container')

####
## Docker
####
build:
	docker-compose up -d --build && \
	ln -s ./docker/postgres/sqitch.conf ./sqitch/sqitch.conf

install:
	docker-compose exec php composer install && \
	docker-compose exec php bin/console import:geo-name:cities && \
	docker-compose exec php bin/console import:geo-name:hierarchy && \
	docker-compose exec php bin/console import:geo-name:country

start:
	docker-compose up -d

stop:
	docker-compose stop

sh:
	docker-compose run $(CONTAINER_NAME) sh

####
## Sqitch
####
sqitch-add:
	docker-compose exec database sqitch add $(SQITCH_VERSION) -n "$(SQITCH_MESSAGE)"

sqitch-tag:
	docker-compose exec database sqitch tag $(SQITCH_VERSION) -n "$(SQITCH_MESSAGE)"

sqitch-rebase:
	docker-compose exec database sqitch rebase

sqitch-deploy:
	docker-compose exec database sqitch deploy
