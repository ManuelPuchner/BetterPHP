#!/bin/bash

# Set the repository and owner
repo="BetterPHP"
owner="ManuelPuchner"

ACCENT_COLOR="\033[1;36m"
RESET_COLOR="\033[0m"


echo "Downloading BetterPHP..."

# Get the latest release information
release=$(curl -s "https://api.github.com/repos/$owner/$repo/releases/latest")

# Extract the download URL for the latest release asset
download_url=$(echo "$release" | jq -r '.tarball_url')

echo $download_url
# Download the asset to the current directory
curl -L "$download_url" -o "$(basename "$download_url")"

echo "Download complete."
echo "Extracting BetterPHP..."

# Extract the downloaded tarball (optional)
tar -xvf "$(basename "$download_url")"

echo "Extraction complete."

echo "Creating betterphp-app"

# generate folder for the app
mkdir -p betterphp-app

# move files to betterphp folder
mv ManuelPuchner-BetterPHP-*/* betterphp-app

# remove downloaded files
rm -rf ManuelPuchner-BetterPHP-*

# remove tarball
rm -rf "$(basename "$download_url")"

echo "BetterPHP app created."

chmod +x betterphp-app/betterphp.sh

touch betterphp-app/src/.env

git init betterphp-app

echo -e "/dist\n*/*.env\n/db-postgres-betterphp" >> betterphp-app/.gitignore

echo -e "Run $ACCENT_COLOR'cd betterphp-app'$RESET_COLOR to enter the app folder."
echo -e "Run $ACCENT_COLOR'./betterphp.sh dev'$RESET_COLOR to start the development environment."

