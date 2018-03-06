#!/bin/bash
SCRIPTPATH="$( cd "$(dirname "$0")" ; pwd -P )"
cd $SCRIPTPATH

chmod +x $SCRIPTPATH/bin/dp

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

echo "Building dockerpilot container..."
docker-compose build

echo "Starting dockerpilot..."
docker-compose up -d

echo "Copying configuration..."
docker exec -it dp-dockerpilot -c "cp /dockerpilot/source/config-example.php /dockerpilot/config.php";

echo "Installing packages..."
docker exec -it dp-dockerpilot -c "composer install --no-dev";
