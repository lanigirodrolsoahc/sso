.PHONY: help up down build rebuild logs clean ps
.DEFAULT_GOAL := help

help: ## \033[1;33m🆘 list available commands\033[0m
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | \
	awk 'BEGIN {FS=":.*?## "} \
	{ \
		desc=$$2; \
		gsub(/\\033/, "\033", desc); \
		printf "  %-10s %s\n", $$1, desc; \
	}'

up: ## \033[32m👟 starts containers\033[0m
	docker-compose up -d

down: ## \033[31m🖐️  stops containers\033[0m
	docker-compose down

build: ## \033[1;32m👷 builds images\033[0m
	docker-compose build

rebuild: clean ## \033[1;31m🏋️  build again without cache, then up\033[0m
	docker-compose build --no-cache
	docker-compose up -d
	$(MAKE) composer

logs: ## \033[1;36m📋 real time logs\033[0m
	docker-compose logs -f

ps: ## \033[36m🛃 services status\033[0m
	docker-compose ps

clean: ## \033[38;5;208m🧼 full clean up, volumes and orphans\033[0m
	docker-compose run --rm php rm -rf /var/www/html/sso/vendor
	rm -f src/sso/composer.lock
	docker-compose down -v --remove-orphans
	docker system prune -f

composer: ## \033[35m🎼 installs composer inside container\033[0m
	docker-compose exec php composer install -d /var/www/html/sso
