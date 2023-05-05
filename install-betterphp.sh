RELEASE_URL=https://github.com/ManuelPuchner/BetterPHP/releases/latest

# Get the latest release information
release=$(curl -s $RELEASE_URL)

# Extract the download URL for the latest release asset
download_url=$(echo "$release" | jq -r '.assets[].browser_download_url' | grep -E '\.tar\.gz$' | head -1)

# Download the asset to the current directory
curl -L "$download_url" -o "$(basename "$download_url")"

# Extract the downloaded tarball (optional)
tar -xvf "$(basename "$download_url")"

