#!/bin/bash

CUR_DIR=$(cd $(dirname $0) && pwd);
#PROJECT_PATH=`readlink -f "$CUR_DIR/../.."`

LIMB_DIR=$1

if [ ! -d $LIMB_DIR ]; then
    echo 'Please pass a full path to limb repo as argument to this script'
    exit 1;
fi

PROJECT_PATH=`readlink -f "$LIMB_DIR"/../`

#LIMB_DIR=`readlink -f "$CUR_DIR/.."`
#LIMB_DIR=$PROJECT_PATH

LIMB_DIR_NAME=`basename "$LIMB_DIR"`

echo $LIMB_DIR_NAME

rm -rf $CUR_DIR/var/*

if [ "$LIMB_DIR_NAME" != "limb" ]; then
    mv $PROJECT_PATH/$LIMB_DIR_NAME $PROJECT_PATH/limb
fi

rm -rf $PROJECT_PATH/limb/bamboo

mkdir -p $PROJECT_PATH/limb/bamboo
cp -r $CUR_DIR/../bamboo_runner/* $PROJECT_PATH/limb/bamboo

php $PROJECT_PATH/limb/bamboo/runner.php

if [ "$LIMB_DIR_NAME" != "limb" ]; then
    mv $PROJECT_PATH/limb $PROJECT_PATH/$LIMB_DIR_NAME
fi
