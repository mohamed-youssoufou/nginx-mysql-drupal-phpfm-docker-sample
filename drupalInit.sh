#!/bin/bash
echo 'YMBL_'

WORKDIR="app"
echo '----INIT SCRIPT CREATE DRUPAL PROJECT V1.0-----'
cd ${WORKDIR}>/dev/null 2>&1
echo $PWD
if [ $(basename $PWD) != $WORKDIR ]; then
   mkdir $WORKDIR
   cd $WORKDIR
fi

#DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

echo 'CHECK REQUIREMENTS ...'
if command -v composer &> /dev/null; then
    echo 'DONE !'
    echo 'ENTER PROJECT NAME:'
    read PROJECT_NAME
    echo 'INIT DRUPAL ...'
    composer create-project drupal/recommended-project $PROJECT_NAME
    echo '------DONE !-------------'
    exit
else
    echo 'Oops!! composer does not exist'
fi

# mkdir ${WORKDIR}
# echo pwd
# cd ${WORKDIR}
# echo $PWD
# touch "server.js"
