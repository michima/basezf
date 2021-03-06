#
# MyProject Makefile
#
# Targets:
#  - clean: 	Remove the staged files
#  - doc: 		Generate the doc
#  - syntax:	Check syntax of PHP files
#  - test: 		Exec unitTest
#  - locale: 	Generate gettext files
#  - update: 	Update from current GIT repository
#
# @copyright  Copyright (c) 2008 BaseZF
# @author     Harold Th�tiot (hthetiot)
# @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

# Binary
ZIP = zip
TAR = tar
PHP = php
DOXYGEN = doxygen

# Project ID
PROJECT_NAME = MyProject
PROJECT_VERSION = alpha
PROJECT_MAINTAINER =
PROJECT_MAINTAINER_COURRIEL = debug@myproject.com
PROJECT_LOCALE_DOMAIN = message
PROJECT_LOCALE_INCLUDE_PATH = $(ROOT)/app $(ROOT)/includes

# Path
ROOT = .
PROJECT_LIB = $(ROOT)/lib
PROJECT_BIN = $(ROOT)/bin
PROJECT_LOG = $(ROOT)/data/log
PROJECT_CONFIG = $(ROOT)/etc

# Locales
LOCALE_SRC_PATH = $(ROOT)/locale
LOCALE_PO_DIR = LC_MESSAGES
LOCALE_DOMAINS = $(PROJECT_LOCALE_DOMAIN) time validate

# Static
CSS_SRC_PATH = $(ROOT)/etc/static/css
CSS_PACK_PATH = $(ROOT)/public/css/pack
JS_SRC_PATH = $(ROOT)/etc/static/js
JS_PACK_PATH = $(ROOT)/public/js/pack

# Others
RELEASE_NAME = $(PROJECT_NAME)-$(PROJECT_VERSION)
CHANGELOG_FILE_PATH = $(ROOT)/CHANGELOG

ZIP_NAME = $(NAME)-$(VERSION).zip
TAR_NAME = $(NAME)-$(VERSION).tar.gz

install: clean syntax locale-deploy static-pack
	@echo "----------------"
	@echo "Project install complete."
	@echo ""

all: clean syntax locale
	@echo "----------------"
	@echo "Project build complete."
	@echo ""

# Generate the doc
doc:
	@echo "----------------"
	@echo "Generate doxygen doc :"
	@$(DOXYGEN) ./etc/doxygen.cnf > $(PROJECT_LOG)/doc.log
	@echo "done"

# Check syntax of PHP files
syntax:
	@echo "----------------"
	@echo "Check PHP syntax on all php files:"
	@for i in =`find . -type f -name *.ph* | tr '\n' ' '`; do test=`php -l $$i`; test2=`echo $$test | grep "Parse error"`; if [ "$$test2" != "" ]; then echo $$test; fi; done;
	@echo "done"

syntax-fast:
	@echo "----------------"
	@echo "Check PHP syntax on all php files updated:"
	@for i in =`git-diff --name-only | grep '.ph' | tr '\n' ' '`; do test=`php -l $$i`; test2=`echo $$test | grep "Parse error"`; if [ "$$test2" != "" ]; then echo $$test; fi; done;
	@echo "done"

# Exec unitTest
test:
	@echo "----------------"
	@echo "Exec Units test:"
	@cd tests && phpunit AllTests
	@echo "done"

config: config-install config-show

# initialise or update config files
config-install:
	@echo "----------------"
	@echo "Configure config files:"
	@$(PROJECT_BIN)/tools/config-generator.php configure $(PROJECT_CONFIG) $(PROJECT_CONFIG)/dist
	@echo "done"

# displaying config files variables values
config-show:
	@echo "----------------"
	@echo "Current config files variables values is:"
	@$(PROJECT_BIN)/tools/config-generator.php show $(PROJECT_CONFIG) $(PROJECT_CONFIG)/dist
	@echo "done"

locale: locale-template locale-update locale-deploy

# Generate .pot file for current project domain
locale-template:
	@echo "----------------"
	@echo "Build GetText POT files for $(PROJECT_NAME):"
	@touch $(LOCALE_SRC_PATH)/dist/$(LOCALE_PO_DIR)/$(PROJECT_LOCALE_DOMAIN).pot
	@find $(PROJECT_LOCALE_INCLUDE_PATH) -type f -iname "*.ph*" | xgettext -L PHP --keyword=__ -j -s -o $(LOCALE_SRC_PATH)/dist/$(LOCALE_PO_DIR)/$(PROJECT_LOCALE_DOMAIN).pot --msgid-bugs-address=$(PROJECT_MAINTAINER_COURRIEL) -f -
	@msguniq $(LOCALE_SRC_PATH)/dist/$(LOCALE_PO_DIR)/$(PROJECT_LOCALE_DOMAIN).pot -o $(LOCALE_SRC_PATH)/dist/$(LOCALE_PO_DIR)/$(PROJECT_LOCALE_DOMAIN).pot
	@echo "done"

# Update .po files of from current .pot for all available local domains
locale-update:
	@echo "----------------"
	@echo "Update GetText PO files from POT files:"
	@for o in $(LOCALE_DOMAINS); do \
	for i in `find $(LOCALE_SRC_PATH) -maxdepth 1 -mindepth 1 -type d -not -name "dist"`; do \
		if [ -e "$$i/$(LOCALE_PO_DIR)/$$o.po" ] ; then \
			echo "Updated $$i/$(LOCALE_PO_DIR)/$$o.po"; \
			msgmerge --previous $$i/$(LOCALE_PO_DIR)/$$o.po $(LOCALE_SRC_PATH)/dist/$(LOCALE_PO_DIR)/$$o.pot -o $$i/$(LOCALE_PO_DIR)/$$o.po; \
			else mkdir $$i/$(LOCALE_PO_DIR)/ -p; msginit -l `echo "$(ROOT)/$$i" | sed 's:$(LOCALE_SRC_PATH)\/::g' | sed 's:\/LC_MESSAGES::g'` --no-translator --no-wrap -i $(LOCALE_SRC_PATH)/dist/$(LOCALE_PO_DIR)/$$o.pot -o $$i/$(LOCALE_PO_DIR)/$$o.po; \
		fi; \
		msguniq $$i/$(LOCALE_PO_DIR)/$$o.po -o $$i/$(LOCALE_PO_DIR)/$$o.po; \
    done \
	done

# Generate all .mo files
locale-deploy:
	@echo "----------------"
	@echo "Generate GetText MO files:"
	@list=`find $(LOCALE_SRC_PATH) -type f -iname "*.po"`; \
	for i in $$list;do \
		echo "Compiling  $$i"; \
		msgfmt --statistics $$i -o `echo $$i | sed s/.po/.mo/`; \
    done

# Remove all .mo and .po files
locale-clean:
	@echo "----------------"
	@echo "Clean GetText MO and PO files:"
	@list=`find $(LOCALE_SRC_PATH) -type f -iname "*.*o"`; \
	for i in $$list;do \
		echo "Removed $$i"; \
		rm -f $$i; \
    done
	@echo "done"

# Static packing
static-pack: static-pack-css static-pack-js

static-pack-css:
	@echo "----------------"
	@./bin/tools/static-pack.sh css $(CSS_SRC_PATH) $(CSS_PACK_PATH)

static-pack-js:
	@echo "----------------"
	@./bin/tools/static-pack.sh js $(JS_SRC_PATH) $(JS_PACK_PATH)

# Remove the log files
log-clean:
	@echo "----------------"
	@echo "Cleaning log files:"
	@list=`find $(PROJECT_LOG) -type f -not -name "README"`; \
	for i in $$list;do \
		echo "Removed $$i"; \
		rm -f $$i; \
    done
	@echo "done"

# Archive the log files
log-archive:
	@echo "----------------"
	@echo "Archive log files:"
	@list=`find $(PROJECT_LOG) -type f -not -name "README"`; \
	for i in $$list;do \
		echo "Archived $$i"; \
    done
	@echo "done"

# Remove the staged files
clean:
	@echo "----------------"
	@echo "Cleaning useless files:"
	@find . -name "*~" -exec rm {} \;
	@echo "done"

# Update from current GIT repository
update:
	@echo "----------------"
	@echo "Update from repository:"
	@git pull

.PHONY: doc
