#!/bin/bash
SCRIPTPATH="$( cd "$(dirname "$0")" ; pwd -P )"
cd $SCRIPTPATH

echo "Adding Dockerpilot to your bash profile..."

LINE="export PATH=$SCRIPTPATH/bin:\$PATH"

if test -e ~/.bash_profile
then
    if grep -Fxq "$LINE" ~/.bash_profile
    then
      echo "Already installed."
    else
      echo $LINE >> ~/.bash_profile
    fi
    echo "Done!"
elif test -e ~/.bash_login
then
    if grep -Fxq "$LINE" ~/.bash_login
    then
        echo "Already installed."
    else
        echo $LINE >> ~/.bash_profile
    fi
    echo "Done!"
elif test -e ~/.profile
then
    if grep -Fxq "$LINE" ~/.profile
    then
      echo "Already installed."
    else
      echo $LINE >> ~/.bash_profile
    fi
    echo "Done!"
else
    echo 'No Bash profile files have been found.'
    echo 'Please create one and add the following line to that file:'
    echo $LINE
fi

cd ../

echo "Copying configuration..."
cp source/config-example.php config.php

echo "Installing packages..."
composer install --no-dev
