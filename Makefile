COVERAGE_DIR=test/coverage

default: test

# Primary targets
clean:
	rm -rf $(COVERAGE_DIR)

distclean: clean
	rm -rf vendor

test: prep phpunit

# Secondary targets from here down
phpunit: $(COVERAGE_DIR)

prep:
	find . -maxdepth 1 -type d -name vendor -empty -delete

vendor/bin/phpunit: vendor

vendor:
	composer install

$(COVERAGE_DIR): vendor/bin/phpunit
	vendor/bin/phpunit --coverage-xml $(COVERAGE_DIR) --verbose --stderr test/unit
