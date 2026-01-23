WITH_DOCKER?=1
COMPOSE=$(shell which docker) compose

ifeq ($(WITH_DOCKER), 1)
	PHP=$(COMPOSE) exec php
else
	PHP=
endif

CONSOLE=$(PHP) php bin/console

jwt:
	$(CONSOLE) lexik:jwt:generate-keypair --overwrite -n

fixtures:
	$(CONSOLE) doctrine:fixtures:load -n

dbReset:
	$(CONSOLE) doctrine:database:drop --force -n
	$(CONSOLE) doctrine:database:create -n
	$(CONSOLE) doctrine:migrations:migrate -n
	$(MAKE) fixtures

tests:
	$(PHP) bin/phpunit

format:
	$(PHP) vendor/bin/mago format

stan:
	$(PHP) vendor/bin/mago analyze
