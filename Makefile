REMOTE=vhost.umonkey.net
FOLDER=hosts/land.umonkey.net

all: assets tags

assets:
	php -f vendor/bin/build-assets themes/land/assets.php

sql:
	sqlite3 -header var/database/database.sqlite3

sql-remote:
	ssh -t $(REMOTE) mysql

log:
	ssh $(REMOTE) tail -F $(FOLDER)/tmp/php.log

pull-db:
	ssh $(REMOTE) mysqldump u468297_pro | pv | mysql
	echo "DELETE FROM sessions;" | mysql

push:
	hg push
	hg push github

shell:
	ssh -t $(REMOTE) cd $(FOLDER) \; bash -l

syntax:
	find config src -name '*.php' -exec php -l {} \;
	phpcs --standard=PSR12 --ignore='src/Migrations' --exclude=Generic.Files.LineLength config src

tags:
	@echo "Rebuilding ctags (see doc/HOWTO_dev.md)"
	@find src vendor/umonkey/ufw1/src -name "*.php" | xargs ctags-exuberant -f .tags -h ".php" -R --totals=yes --tag-relative=yes --PHP-kinds=+cf --regex-PHP='/abstract class ([^ ]*)/\1/c/' --regex-PHP='/interface ([^ ]*)/\1/c/' --regex-PHP='/(public |static |abstract |protected |private )+function ([^ (]*)/\2/f/' >/dev/null 2>&1

upgrade:
	composer upgrade
	hg addremove vendor composer.*

deploy: assets
	rsync -avz -e ssh --delete --exclude .hg bin config public src themes vendor $(REMOTE):$(FOLDER)

update-ufw:
	hg --cwd vendor/umonkey/ufw1/ up -C
	hg --cwd vendor/umonkey/ufw1/ clean
	composer update umonkey/ufw1
	hg ci composer.lock -m "Dependency update: umonkey/ufw1"

push-ufw:
	rsync -avz -e --delete --exclude .hg vendor/umonkey/ufw1/ ~/src/ufw1/
	cd ~/src/ufw1/ && bash -l

.PHONY: assets tags sql schema
