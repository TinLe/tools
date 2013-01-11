#!/bin/bash -e

# parselog.pl wrapper

PARSELOG=/usr/local/bin/parselog.pl
MELODISLOG=/melodis/log/melodis.log
LINES=500000

tail -${LINES} ${MELODISLOG} | ${PARSELOG} -
