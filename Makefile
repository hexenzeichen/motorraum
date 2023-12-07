test:
	./vendor/bin/phpunit ./tests

run: 
	./run.php abcd

fix:
	./vendor/bin/php-cs-fixer fix src

sniff:
	./vendor/bin/phpcs src

beauty:
	./vendor/bin/phpcbf src

analysis:
	./vendor/bin/phpstan analyze -l 6 src

