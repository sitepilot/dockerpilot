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
elif test -e ~/.bash_login
then
    if grep -Fxq "$LINE" ~/.bash_login
    then
        echo "Already installed."
    else
        echo $LINE >> ~/.bash_profile
    fi
elif test -e ~/.profile
then
    if grep -Fxq "$LINE" ~/.profile
    then
      echo "Already installed."
    else
      echo $LINE >> ~/.bash_profile
    fi
else
    echo 'No Bash profile files have been found.'
    echo 'Please create one and add the following line to that file:'
    echo $LINE
fi

if [ "$(docker ps -q -f name=dp-dockerpilot)" ]; then
    echo "Stopping and removing Dockerpilot container..."
    docker stop dp-dockerpilot
    docker rm dp-dockerpilot
fi

echo "Building dockerpilot container..."
docker build dockerfiles/dockerpilot -t dockerpilot --build-arg USER=dockerpilot -t dockerpilot --force-rm

echo "Starting dockerpilot..."
docker run --name dp-dockerpilot --restart always -d -v /var/run/docker.sock:/var/run/docker.sock -v $PWD/../:/dockerpilot dockerpilot

if [ ! -f ../config.php ]; then
    echo "Copying configuration..."
    docker exec -i dp-dockerpilot bash -c "cd source && cp -n config-example.php ../config.php"

    cd ../
    echo "Saving server path..."
    echo "// Dockerpilot server path (for mounting volumes)" >> config.php
    echo "define('SERVER_PATH', '$PWD');" >> config.php
fi

echo "Installing packages..."
docker exec -it dp-dockerpilot composer install --no-dev

echo "Dockerpilot is installed, open a new terminal and type 'dp' to see a list of commands."
