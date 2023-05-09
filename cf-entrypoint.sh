#!/bin/bash

set -e

if [ $DEBUG == '1' ]; then
  set -x
fi

php vcap.php
source .cf.env
rm -f .cf.env
rm -f vcap.php

/docker-entrypoint.sh $@
