#!/bin/bash

# check if php is installed and has version >= 8.0
if ! command -v php &> /dev/null
then
    echo "PHP could not be found"
    exit
fi


if [[ $(php -r "echo version_compare(PHP_VERSION, '8.0.0');") == "-1" ]];
then
    echo "PHP version must be >= 8.0"
    exit
fi


checkBetterphpInstallation()
{
  # check if betterphp directory exists
  if [ ! -d "./betterphp" ]; then
    echo "Betterphp is not installed. Please run 'betterphp install' to install it."
    exit
  fi
}

checkDockerInstallation()
{
  # check if docker is installed
  if ! command -v docker &> /dev/null
  then
      echo "Docker could not be found"
      exit
  fi
}

function checkDevContainersRunning {
  ispostgresrunning=$(docker inspect -f '{{.State.Running}}' postgres-betterphp)
  ispgadminrunning=$(docker inspect -f '{{.State.Running}}' pgadmin-betterphp)
  isapacherunning=$(docker inspect -f '{{.State.Running}}' web-betterphp)

  if [ "$ispostgresrunning" == "true" ] && [ "$ispgadminrunning" == "true" ] && [ "$isapacherunning" == "true" ]; then
    return 0
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
        echo "Error: Could not install inotify-tools. Unsupported package manager."
        exit 1
      fi
      ;;
    *)
      echo "Error: Unsupported operating system."
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
    echo "Error: Could not install fswatch. Homebrew is not installed."
    exit 1
  fi
}

function checkFswatchInstallation {
  # check if fswatch is installed
  if ! command -v fswatch &> /dev/null
  then
      echo "fswatch could not be found"
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
        fi
      done
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

    echo "You can now access the application at http://localhost:8080"

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

      echo "You can now access the application at http://localhost:8080"

      echo "Watching for changes..."
      watchDirectoryAndRebuild "./src" "php ./betterphp/cli/index.php"

    else
      echo "Development environment could not be started."
    fi

    exit;
  fi
  exit
else
  echo "No argument provided. Please run 'betterphp dev' to start the development server."
  exit
fi
