SQITCH_VERSION ?= $(shell bash -c 'read -p "Version: " version; echo $$version')
SQITCH_MESSAGE ?= $(shell bash -c 'read -p "Message: " message; echo $$message')

sqitch-add:
	docker-compose exec database sqitch add $(SQITCH_VERSION) -n "$(SQITCH_MESSAGE)"

sqitch-rebase:
	docker-compose exec database sqitch rebase

sqitch-deploy:
	docker-compose exec database sqitch deploy
