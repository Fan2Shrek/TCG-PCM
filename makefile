WITH_DOCKER?=1
COMPOSE=$(shell which docker) compose

ifndef env
env=dev
endif

ifeq ($(WITH_DOCKER), 1)
	PHP=$(COMPOSE) exec php
else
	PHP=cd api &&
endif

CONSOLE=$(PHP) php bin/console --env=$(env)

cardList:
	$(CONSOLE) app:update:card-list

jwt:
	$(CONSOLE) lexik:jwt:generate-keypair --overwrite -n

fixtures:
	$(CONSOLE) doctrine:fixtures:load -n

dbReset:
	$(CONSOLE) doctrine:database:drop --force -n --if-exists
	$(CONSOLE) doctrine:database:create -n
	$(CONSOLE) doctrine:migrations:migrate -n --allow-no-migration
	$(CONSOLE) doctrine:schema:update --force -n
	$(MAKE) fixtures

setup-tests:
	$(MAKE) jwt
	$(MAKE) dbReset env=test

tests:
	$(PHP) bin/phpunit

tests-replay:
	$(PHP) bin/phpunit --group replay

format:
	$(PHP) vendor/bin/mago format

format-dry-run:
	$(PHP) vendor/bin/mago format --dry-run

format-check:
	$(PHP) vendor/bin/mago format --check

lint:
	$(PHP) vendor/bin/mago lint

lint-fix:
	$(PHP) vendor/bin/mago lint --fix --unsafe

symfony-lint:
	$(CONSOLE) lint:container
	$(CONSOLE) debug:container --deprecations

stan:
	$(PHP) vendor/bin/mago analyze
