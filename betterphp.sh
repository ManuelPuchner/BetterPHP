#!/bin/bash

ACCENT_COLOR="\033[0;36m"
RESET_COLOR="\033[0m"
ERROR_COLOR="\033[1;31m"

execute_cleanup=true

cleanup() {
  if [ "$execute_cleanup" = false ]; then
    return
  fi
  echo ""
  echo "Cleaning up..."
  if checkDevContainersRunning; then
    echo "Stopping development environment..."
    docker compose -f docker-compose-dev-environment.yaml down
  fi
  echo "Done."
}

trap cleanup EXIT


# check if php is installed and has version >= 8.0
if ! command -v php &> /dev/null
then
    echo -e "$ERROR_COLOR PHP could not be found$RESET_COLOR"
    exit
fi


if [[ $(php -r "echo version_compare(PHP_VERSION, '8.0.0');") == "-1" ]];
then
    echo -e "$ERROR_COLOR PHP version must be >= 8.0$RESET_COLOR"
    exit
fi


checkBetterphpInstallation()
{
  # check if betterphp directory exists
  if [ ! -d "./betterphp" ]; then
    echo -e "$ERROR_COLOR Betterphp is not installed.$RESET_COLOR Please run 'betterphp install' to install it."
    exit
  fi
}

checkDockerInstallation()
{
  # check if docker is installed
  if ! command -v docker &> /dev/null
  then
      echo -e "$ERROR_COLOR Docker could not be found$RESET_COLOR"
      exit
  fi
}

function checkProdContainersRunning {
  if docker container inspect postgres-betterphp-3ahitm >/dev/null 2>&1 &&
     docker container inspect web-betterphp-3ahitm >/dev/null 2>&1; then
    ispostgresrunning=$(docker inspect -f '{{.State.Running}}' postgres-betterphp-3ahitm)
    isapacherunning=$(docker inspect -f '{{.State.Running}}' web-betterphp-3ahitm)

    if [ "$ispostgresrunning" == "true" ]  && [ "$isapacherunning" == "true" ]; then
      return 0
    else
      return 1
    fi
  else
    return 1
  fi
}

function checkDevContainersRunning {
  if docker container inspect postgres-betterphp-3ahitm >/dev/null 2>&1 &&
     docker container inspect pgadmin-betterphp-3ahitm >/dev/null 2>&1 &&
     docker container inspect web-betterphp-3ahitm >/dev/null 2>&1; then
    ispostgresrunning=$(docker inspect -f '{{.State.Running}}' postgres-betterphp-3ahitm)
    ispgadminrunning=$(docker inspect -f '{{.State.Running}}' pgadmin-betterphp-3ahitm)
    isapacherunning=$(docker inspect -f '{{.State.Running}}' web-betterphp-3ahitm)

    if [ "$ispostgresrunning" == "true" ] && [ "$ispgadminrunning" == "true" ] && [ "$isapacherunning" == "true" ]; then
      return 0
    else
      return 1
    fi
  else
    return 1
  fi
}


function installInotifyAwait() {
  # Check the operating system
  local os=$(uname -s)
  case "$os" in
    Linux*)
      # Install inotify-tools on Linux
      if command -v apt-get >/dev/null 2>&1; then
        sudo apt-get update
        sudo apt-get install -y inotify-tools
      elif command -v yum >/dev/null 2>&1; then
        sudo yum install -y inotify-tools
      elif command -v dnf >/dev/null 2>&1; then
        sudo dnf install -y inotify-tools
      else
        echo -e "$ERROR_COLOR Error: Could not install inotify-tools.$RESET_COLOR Unsupported package manager."
        exit 1
      fi
      ;;
    *)
      echo -e "$ERROR_COLOR Error: Unsupported operating system."
      exit 1
      ;;
  esac
}

function checkInotifywaitInstallation {
  # check if inotifywait is installed
  if ! command -v inotifywait &> /dev/null
  then
      echo "inotifywait could not be found"
      echo "trying to install inotifywait..."
      installInotifyAwait
      exit
  fi
}

function installFswatch {
  # Check if Homebrew is installed
  if command -v brew >/dev/null 2>&1; then
    # Install fswatch using Homebrew
    brew install fswatch
  else
    echo -e "$ERROR_COLOR Error: Could not install fswatch.$RESET_COLOR Homebrew is not installed."
    exit 1
  fi
}

function checkFswatchInstallation {
  # check if fswatch is installed
  if ! command -v fswatch &> /dev/null
  then
      echo -e "$ACCENT_COLOR fswatch could not be found$RESET_COLOR"
      echo "trying to install fswatch..."
      installFswatch
      exit
  fi
}

function watchDirectoryAndRebuild() {
  # Set the directory to monitor and the command to run
  local DIR_TO_WATCH="$1"
  local COMMAND_TO_RUN="$2"

  if [[ "$OSTYPE" == "darwin"* ]]; then
    checkFswatchInstallation
    # macOS: Use fsevents to monitor the directory
    fswatch -r "$DIR_TO_WATCH" |
      while read path action file; do
        # Run the command when a file is modified
        echo "Running $COMMAND_TO_RUN on $file"
        eval "$COMMAND_TO_RUN $path$file"
        echo -e "$ACCENT_COLOR" + "You can now access the application at http://localhost:8080 $RESET_COLOR"
      done
  else
    checkInotifywaitInstallation
    # Other OS: Use inotifywait to monitor the directory
    inotifywait -m -e modify "$DIR_TO_WATCH" |
      while read path action file; do
        # Run the command when a file is modified
        if [[ "$file" != *.swp ]]; then  # Ignore Vim swap files
          echo "Running $COMMAND_TO_RUN on $file"
          eval "$COMMAND_TO_RUN $DIR_TO_WATCH/$file"
          echo -e "$ACCENT_COLOR" + "You can now access the application at http://localhost:8080 $RESET_COLOR"
        fi
      done
  fi
}

function checkPgAdminRunning {
  if docker container inspect pgadmin-betterphp-3ahitm >/dev/null 2>&1; then
    ispgadminrunning=$(docker inspect -f '{{.State.Running}}' pgadmin-betterphp-3ahitm)

    if [ "$ispgadminrunning" == "true" ]; then
      return 0
    else
      return 1
    fi
  else
    return 1
  fi
}


checkBetterphpInstallation
checkDockerInstallation

ARG=$1

# check if argument is dev
if [ "$ARG" == "dev" ]; then


  # check if dev containers are running
  if checkDevContainersRunning; then
    echo "Development environment is already running."
    echo "Building application..."
    php ./betterphp/cli/index.php

    echo -e "$ACCENT_COLOR You can now access the application at http://localhost:8080 $RESET_COLOR"

    echo "Watching for changes..."
    watchDirectoryAndRebuild "./src" "php ./betterphp/cli/index.php"

    exit
  else
    echo "Starting development environment..."
    docker compose -f docker-compose-dev-environment.yaml up -d

    if checkDevContainersRunning; then
      echo "Development environment started successfully."
      echo "Building application..."
      php ./betterphp/cli/index.php

      echo -e "$ACCENT_COLOR" + "You can now access the application at http://localhost:8080 $RESET_COLOR"

      echo "Watching for changes..."
      watchDirectoryAndRebuild "./src" "php ./betterphp/cli/index.php"

    else
       echo -e "$ERROR_COLOR Development environment could not be started.$RESET_COLOR"
    fi

    exit;
  fi
  exit
elif [ "$ARG" == "db" ]; then
    DB_ARG=$2

    if [ "$DB_ARG" == "generate" ]; then
        echo "Generating create statements..."
        rm -rf ./dist/sql/create.sql
        php ./betterphp/cli/orm/generateTables.php
        echo -e "$ACCENT_COLOR Done.$RESET_COLOR"
        exit
    else
        echo -e "$ERROR_COLOR No argument provided.$RESET_COLOR Please run 'betterphp db generate' to generate create statements."
        exit
    fi
elif [ "$ARG" == "build" ]; then
  echo "Compiling api..."
  php ./betterphp/cli/index.php
  echo -e "$ACCENT_COLOR Done.$RESET_COLOR"
  exit
elif [ "$ARG" == "start" ]; then
  echo "Starting production application..."

  # check if production containers are running
  if checkProdContainersRunning; then
    echo "Production environment is already running."
    echo -e "$ACCENT_COLOR You can now access the application at http://localhost:8080 $RESET_COLOR"
    exit
  else
    echo "Starting production environment..."
    docker compose -f docker-compose-dev-environment.yaml up -d

    # stop and remove pgadmin if it is running
    if checkPgAdminRunning; then
      echo "Stopping pgadmin..."
      docker stop pgadmin-betterphp-3ahitm
      docker rm pgadmin-betterphp-3ahitm
    fi

    if checkProdContainersRunning; then
      echo "Production environment started successfully."
      echo -e "$ACCENT_COLOR You can now access the application at http://localhost:8080 $RESET_COLOR"
    else
       echo -e "$ERROR_COLOR Production environment could not be started.$RESET_COLOR"
    fi

    exit;
  fi

  echo -e "$ACCENT_COLOR Done.$RESET_COLOR"
  exit
elif [ "$ARG" == "stop" ]; then
  echo "Stopping production environment..."

  # stop and remove pgadmin if it is running
  if checkPgAdminRunning; then
    echo "Stopping pgadmin..."
    docker stop pgadmin-betterphp-3ahitm
    docker rm pgadmin-betterphp-3ahitm
  fi

  # stop and remove production containers
  if checkProdContainersRunning; then
    echo "Stopping production environment..."
    docker compose -f docker-compose-dev-environment.yaml down
    echo -e "$ACCENT_COLOR Done.$RESET_COLOR"
    exit
  else
    echo "Production environment is not running."
    echo -e "$ACCENT_COLOR Done.$RESET_COLOR"
    exit
  fi
elif [ "$ARG" == "help" ]; then
  echo "Available commands:"
  echo "  betterphp dev"
  echo "  betterphp db generate"
  echo "  betterphp build"
  echo "  betterphp start"
  echo "  betterphp stop"
  echo "  betterphp help"
  exit
else
  echo -e "$ERROR_COLOR No argument provided.$RESET_COLOR Please run 'betterphp dev' to start the development server."
  exit
fi



