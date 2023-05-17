#!/bin/sh

set -ex

IMAGE_TAG=`uuidgen`
DIRNAME=`dirname $0`


cd $DIRNAME
export VCAP_SERVICES=`cat vcap-services.json`

cd ..
docker build --no-cache -t $IMAGE_TAG .
docker run  -e DEBUG=1 -e VCAP_SERVICES="$VCAP_SERVICES" -e PORT=5050 -p 5050:5050 $IMAGE_TAG