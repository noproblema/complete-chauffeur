#!/bin/bash

# WordPress Plugin Security Hardener
# Adds direct execution prevention to all PHP files in a WordPress plugin
# Usage: ./secure_plugins.sh /path/to/plugin/directory

# Security header to add to PHP files
SECURITY_HEADER=$(cat << 'EOF'
<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
EOF
)

# Check if plugin directory is provided
if [ -z "$1" ]; then
    echo "Usage: $0 /path/to/plugin/directory"
    exit 1
fi

PLUGIN_DIR="$1"

# Verify the directory exists
if [ ! -d "$PLUGIN_DIR" ]; then
    echo "Error: Directory $PLUGIN_DIR does not exist"
    exit 1
fi

# Find all PHP files in the plugin directory (excluding vendor directory if it exists)
find "$PLUGIN_DIR" -type f -name "*.php" ! -path "*/vendor/*" | while read -r file; do
    # Check if the file already has the security check
    if ! grep -q "defined('ABSPATH')" "$file"; then
        # Add the security header after the opening PHP tag
        # Handle files that might have a shebang line
        if head -n 1 "$file" | grep -q "^#!"; then
            # File has shebang, insert after first line
            sed -i "1a\\
$SECURITY_HEADER" "$file"
        else
            # Regular PHP file, insert after opening tag
            # First create a temporary file with the new content
            TEMP_FILE=$(mktemp)
            echo "$SECURITY_HEADER" > "$TEMP_FILE"
            tail -n +2 "$file" >> "$TEMP_FILE"
            mv "$TEMP_FILE" "$file"
        fi
        echo "Secured: $file"
    else
        echo "Already secured: $file"
    fi
done

echo "Security hardening complete for plugin in $PLUGIN_DIR"
