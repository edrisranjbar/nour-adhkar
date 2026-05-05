#!/bin/bash

# Script to generate Android keystore for app signing
# This will use the passwords from key.properties

cd "$(dirname "$0")"

# Read passwords from key.properties
if [ ! -f "key.properties" ]; then
    echo "Error: key.properties file not found!"
    exit 1
fi

STORE_PASSWORD=$(grep "storePassword=" key.properties | cut -d'=' -f2)
KEY_PASSWORD=$(grep "keyPassword=" key.properties | cut -d'=' -f2)
KEY_ALIAS=$(grep "keyAlias=" key.properties | cut -d'=' -f2)

if [ -z "$STORE_PASSWORD" ] || [ -z "$KEY_PASSWORD" ]; then
    echo "Error: Passwords not found in key.properties"
    exit 1
fi

echo "Generating keystore..."
echo "Key alias: $KEY_ALIAS"
echo "Keystore file: upload-keystore.jks"
echo ""

keytool -genkey -v \
    -keystore upload-keystore.jks \
    -keyalg RSA \
    -keysize 2048 \
    -validity 10000 \
    -alias "$KEY_ALIAS" \
    -storepass "$STORE_PASSWORD" \
    -keypass "$KEY_PASSWORD" \
    -dname "CN=Nour Adhkar, OU=Mobile, O=Your Company, L=City, ST=State, C=IR"

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Keystore generated successfully!"
    echo "File location: $(pwd)/upload-keystore.jks"
    echo ""
    echo "⚠️  IMPORTANT: Keep this keystore file and passwords safe!"
    echo "You'll need them for all future app updates."
else
    echo ""
    echo "✗ Error generating keystore"
    exit 1
fi

