#
# (C) Copyright 2016-2018 Tin Le tin@le.org
# All Rights Reserved
#
ROOT_DIR=$(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))

.PHONEY: all
all: build

.PHONEY: pex
build: wheel
	@echo Building name2hosts.pex....
	# pex --python=python3 -f $(ROOT_DIR) *.whl -r $(ROOT_DIR)/requirements.txt -e name2hosts.name2hosts -o name2hosts.pex
	pex --python=python2 -f $(ROOT_DIR) *.whl -r $(ROOT_DIR)/requirements.txt -e name2hosts.name2hosts -o name2hosts.pex

wheel:
	# pip3 wheel -w $(ROOT_DIR) $(ROOT_DIR)
	pip2 wheel -w $(ROOT_DIR) $(ROOT_DIR)

install: build
	@echo Installing name2hosts.pex in /usr/local/bin....
	@if test -f /usr/local/bin/name2hosts; then \
		sudo mv /usr/local/bin/name2hosts /usr/local/bin/name2hosts.old; \
	fi
	@sudo cp name2hosts.pex /usr/local/bin/name2hosts

clean:
	rm -f name2hosts.pex *.whl
