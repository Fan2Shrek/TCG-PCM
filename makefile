WITH_DOCKER?=1
COMPOSE=$(shell which docker) compose

STACK_NAME=tcg

ifndef env
env=dev
endif

ifeq ($(WITH_DOCKER), 1)
	PHP=$(COMPOSE) exec php
else
	PHP=cd api &&
endif

CONSOLE=$(PHP) php bin/console --env=$(env)

COVERAGE_DIR=var/phpunit

install:
	$(COMPOSE) exec php composer install

card-list:
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
	@$(PHP) rm -rf var/cache/test
	$(PHP) bin/phpunit

tests-coverage:
	make remove-cache
	$(PHP) bin/phpunit --coverage-html=$(COVERAGE_DIR)

tests-ci:
	make remove-cache
	$(PHP) bin/phpunit \
		--log-junit var/junit.xml \
		--coverage-clover var/coverage.xml

tests-replay:
	$(PHP) bin/phpunit --group replay

remove-cache:
	@rm -rf api/var

clean:
	@rm -rf api/var
	@rm -rf front/.next front/node_modules
	docker builder prune -f

build-api:
	docker build --no-cache -t tcg-api:prod --target prod ./api

build-api-dev:
	docker build --no-cache -t tcg-api:dev --target dev ./api

build-front:
	docker build --no-cache -t tcg-front:prod --target prod ./front

build-front-dev:
	docker build --no-cache -t tcg-front:dev --target dev ./front

pull:
	docker pull ghcr.io/fan2shrek/tcg/api:latest
	docker pull ghcr.io/fan2shrek/tcg/frontend:latest
	docker pull mariadb:11.8.2
	docker pull redis:7
	docker pull dunglas/mercure
	docker pull amir20/dozzle:latest

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

# ===== Docker Swarm =====

swarm-init:
	docker swarm init

secrets-create:
	@echo "Entrez le mot de passe root MariaDB puis Ctrl+D :"
	docker secret create db_root_password -

secrets-list:
	docker secret ls

stack-deploy:
	docker stack deploy -c stack.yml $(STACK_NAME) --with-registry-auth

stack-ps:
	docker stack ps $(STACK_NAME)

SERVICE?=$(STACK_NAME)_php
stack-logs:
	docker service logs $(SERVICE) -f

stack-rm:
	docker stack rm $(STACK_NAME)
