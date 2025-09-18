.PHONY: install up test

install:
	cd src && composer install
	cd src && cp .env.example .env 2>/dev/null || true
	$(MAKE) up

up:
	cd src && ./vendor/bin/sail up -d

test:
	cd src && ./vendor/bin/sail test
