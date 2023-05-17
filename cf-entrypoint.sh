#!/bin/bash

set -e

if [ "$DEBUG" = "1" ]; then
  set -x
fi

INIT_SCRIPT_FILE='vcap.php'
CF_ENV_FILE='.cf.env'

# Run the init scripts
php $INIT_SCRIPT_FILE
source $CF_ENV_FILE

# Delete the init scripts
rm -f $CF_ENV_FILE
rm -f $INIT_SCRIPT_FILE

/docker-entrypoint.sh $@
