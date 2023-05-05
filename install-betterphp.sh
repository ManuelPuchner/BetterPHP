#!/bin/bash

# Set the repository and owner
repo="BetterPHP"
owner="ManuelPuchner"


# Get the latest release information
release=$(curl -s "https://api.github.com/repos/$owner/$repo/releases/latest")

# Extract the download URL for the latest release asset
download_url=$(echo "$release" | jq -r '.tarball_url')

echo $download_url
# Download the asset to the current directory
curl -L "$download_url" -o "$(basename "$download_url")"


# Extract the downloaded tarball (optional)
tar -xvf "$(basename "$download_url")"

# generate folder for the app
mkdir -p betterphp-app

# move files to betterphp folder
mv ManuelPuchner-BetterPHP-*/* betterphp-app

# remove downloaded files
rm -rf ManuelPuchner-BetterPHP-*

# remove tarball
rm -rf "$(basename "$download_url")"

