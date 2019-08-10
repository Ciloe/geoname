SQITCH_VERSION ?= $(shell bash -c 'read -p "Version: " version; echo $$version')
SQITCH_MESSAGE ?= $(shell bash -c 'read -p "Message: " message; echo $$message')

build:
	docker-compose up -d --build && \
	ln -s ./docker/postgres/sqitch.conf ./sqitch/sqitch.conf

start:
	docker-compose up -d

stop:
	docker-compose stop

sqitch-add:
	docker-compose exec database sqitch add $(SQITCH_VERSION) -n "$(SQITCH_MESSAGE)"

sqitch-tag:
	docker-compose exec database sqitch tag $(SQITCH_VERSION) -n "$(SQITCH_MESSAGE)"

sqitch-rebase:
	docker-compose exec database sqitch rebase

sqitch-deploy:
	docker-compose exec database sqitch deploy
