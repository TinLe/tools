#!/bin/bash

PATH=/home/tin/.local/bin:$PATH

# remove all indices older than 30 days
#curator delete indices --older-than 60 --time-unit days --timestring '%Y.%m.%d'

# remove all indices older than 9 weeks
# curator delete indices --older-than 9 --time-unit weeks --timestring '%Y.%w'

# optimize all indices older than today(current)
#curator optimize indices --older-than 1 --time-unit days --timestring '%Y.%m.%d'
curator ~tin/.curator/actions/forcemerge

# remove all indices older than 3 month
#curator delete indices --older-than 3 --time-unit months --timestring '%Y.%m'
curator ~tin/.curator/actions/delete_indices_3months

# remove all indices older than 7 days
# curator delete indices --older-than 7 --time-unit days --timestring '%Y.%m.%d'
curator ~tin/.curator/actions/delete_indices_7days
