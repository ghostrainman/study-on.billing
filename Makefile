COMPOSE=docker compose
PHP=$(COMPOSE) exec php
CONSOLE=$(PHP) bin/console
COMPOSER=$(PHP) composer

up:
	@${COMPOSE} up -d

down:
	@${COMPOSE} down

clear:
	@${CONSOLE} cache:clear

migration:
	@${CONSOLE} make:migration

migrate:
	@${CONSOLE} doctrine:migrations:migrate  --no-interaction

fixtload:
	@${CONSOLE} doctrine:fixtures:load  --no-interaction

phpunit:
	@${PHP} bin/phpunit

prepare_dev:
	@${CONSOLE} doctrine:database:create --if-not-exists
	@${CONSOLE} doctrine:migrations:migrate --no-interaction
	@${CONSOLE} doctrine:fixtures:load --no-interaction

prepare_test_db:
	@${CONSOLE} doctrine:database:drop --env=test --force
	@${CONSOLE} doctrine:database:create --env=test
	@${CONSOLE} doctrine:migrations:migrate --env=test --no-interaction
	@${CONSOLE} doctrine:fixtures:load --env=test --no-interaction

test: prepare_test_db
	@${PHP} bash -c "APP_ENV=test php bin/phpunit"

# В файл local.mk можно добавлять дополнительные make-команды,
# которые требуются лично вам, но не нужны на проекте в целом
-include local.mk
