WITH_DOCKER?=1
COMPOSE=$(shell which docker) compose

STACK_NAME=tcg
INFISICAL_DOMAIN=https://infisical-esgi.wiatr.fr
INFISICAL_PROJECT_ID=659c8dc2-1cee-414f-8fd7-70b1ab9b537c
INFISICAL_ENV=production

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

# ===== Dev =====

up:
	$(COMPOSE) up -d --build --remove-orphans
	$(PHP) composer install
	@echo "Waiting for database..."
	@$(PHP) bash -c 'until echo > /dev/tcp/db/3306 2>/dev/null; do sleep 1; done'
	@$(PHP) sh -c 'test -f config/jwt/private.pem || php bin/console lexik:jwt:generate-keypair -n'
	$(CONSOLE) doctrine:schema:update --force -n

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
	$(MAKE) remove-cache
	$(PHP) bin/phpunit --coverage-html=$(COVERAGE_DIR)

tests-ci:
	$(MAKE) remove-cache
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

# ===== Build =====

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
	docker pull ghcr.io/fan2shrek/tcg/db-backup:latest
	docker pull mariadb:11.8.2
	docker pull redis:7
	docker pull dunglas/mercure
	docker pull amir20/dozzle:latest
	docker pull infisical/infisical:latest-postgres
	docker pull postgres:16-alpine

# ===== Code quality =====

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

infisical-login:
	@set -a && . /root/.env.infisical && set +a && \
	infisical login \
		--method=universal-auth \
		--client-id=$$INFISICAL_CLIENT_ID \
		--client-secret=$$INFISICAL_CLIENT_SECRET \
		--domain=$(INFISICAL_DOMAIN) \
		--plain

infisical-secrets-create:
	@openssl rand -hex 16 | docker secret create infisical_encryption_key -
	@openssl rand -base64 32 | docker secret create infisical_auth_secret -
	@openssl rand -base64 32 | docker secret create infisical_db_password -
	@echo "Secrets Infisical créés."

secrets-restore:
	@set -a && . /root/.env.infisical && set +a && \
	TOKEN=$$(infisical login \
		--method=universal-auth \
		--client-id=$$INFISICAL_CLIENT_ID \
		--client-secret=$$INFISICAL_CLIENT_SECRET \
		--domain=$(INFISICAL_DOMAIN) \
		--plain) && \
	infisical secrets get DB_ROOT_PASSWORD \
		--token=$$TOKEN \
		--domain=$(INFISICAL_DOMAIN) \
		--projectId=$(INFISICAL_PROJECT_ID) \
		--plain \
	| docker secret create db_root_password -

dozzle-secret-create:
	@set -a && . /root/.env.infisical && set +a && \
	TOKEN=$$(infisical login \
		--method=universal-auth \
		--client-id=$$INFISICAL_CLIENT_ID \
		--client-secret=$$INFISICAL_CLIENT_SECRET \
		--domain=$(INFISICAL_DOMAIN) \
		--plain) && \
	PASSWORD=$$(infisical secrets get DOZZLE_PASSWORD \
		--token=$$TOKEN \
		--domain=$(INFISICAL_DOMAIN) \
		--projectId=$(INFISICAL_PROJECT_ID) \
		--plain) && \
	HASH=$$(htpasswd -bnBC 10 "" "$$PASSWORD" | tr -d ':\n' | sed 's/\$$2y/\$$2a/') && \
	printf 'users:\n  admin:\n    name: Admin\n    email: admin@wiatr.fr\n    password: "%s"\n' "$$HASH" \
	| docker secret create dozzle_users -
	@echo "Secret Dozzle créé."

dozzle-secret-rotate:
	-docker secret rm dozzle_users
	$(MAKE) dozzle-secret-create

secrets-list:
	docker secret ls

networks:
	docker network inspect app >/dev/null 2>&1 || docker network create --driver overlay --attachable app
	docker network inspect infisical >/dev/null 2>&1 || docker network create --driver overlay --attachable infisical

stack-deploy-backup:
	docker stack deploy -c stack.backup.yml backup --with-registry-auth

stack-deploy-infisical:
	docker stack deploy -c stack.infisical.yml infisical --with-registry-auth

stack-deploy:
	@set -a && . /root/.env.infisical && set +a && \
	TOKEN=$$(infisical login \
		--method=universal-auth \
		--client-id=$$INFISICAL_CLIENT_ID \
		--client-secret=$$INFISICAL_CLIENT_SECRET \
		--domain=$(INFISICAL_DOMAIN) \
		--plain) && \
	infisical run \
		--token=$$TOKEN \
		--domain=$(INFISICAL_DOMAIN) \
		--projectId=$(INFISICAL_PROJECT_ID) \
		-- docker stack deploy -c stack.yml $(STACK_NAME) --with-registry-auth

stack-ps:
	docker stack ps $(STACK_NAME)

SERVICE?=$(STACK_NAME)_php
stack-logs:
	docker service logs $(SERVICE) -f

stack-rm:
	docker stack rm $(STACK_NAME)

stack-rm-infisical:
	docker stack rm infisical

stack-rm-backup:
	docker stack rm backup
