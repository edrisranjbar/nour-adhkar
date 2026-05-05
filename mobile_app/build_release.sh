#!/bin/bash
# Build script for Production Release
# Creates AAB and Cafe Bazar BIN files
# Automatically reads version from pubspec.yaml

set -e  # Exit on error

APP_NAME="nour_adhkar"

echo "🔨 Building Nour Adhkar - Production Release"
echo "============================================="

# Read version from pubspec.yaml
if [ ! -f "pubspec.yaml" ]; then
    echo "❌ pubspec.yaml not found!"
    exit 1
fi

VERSION_LINE=$(grep "^version:" pubspec.yaml | sed 's/version: //' | sed 's/ //g')

if [ -z "$VERSION_LINE" ]; then
    echo "❌ Could not find version in pubspec.yaml"
    exit 1
fi

# Parse version (format: "1.1.2+10" -> VERSION="1.1.2", BUILD_NUMBER="10")
# Split by '+' to separate version and build number
if [[ "$VERSION_LINE" == *"+"* ]]; then
    VERSION=$(echo "$VERSION_LINE" | cut -d'+' -f1)
    BUILD_NUMBER=$(echo "$VERSION_LINE" | cut -d'+' -f2)
    
    # Validate version format (X.Y.Z)
    if ! [[ "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "❌ Invalid version format in pubspec.yaml: $VERSION"
        echo "   Expected format: X.Y.Z+BUILD (e.g., 1.1.2+10)"
        exit 1
    fi
    
    # Validate build number (numeric)
    if ! [[ "$BUILD_NUMBER" =~ ^[0-9]+$ ]]; then
        echo "❌ Invalid build number in pubspec.yaml: $BUILD_NUMBER"
        echo "   Build number must be numeric"
        exit 1
    fi
else
    echo "❌ Invalid version format in pubspec.yaml: $VERSION_LINE"
    echo "   Expected format: X.Y.Z+BUILD (e.g., 1.1.2+10)"
    exit 1
fi

echo "📋 Version from pubspec.yaml: ${VERSION}+${BUILD_NUMBER}"
echo "   Version Name: ${VERSION}"
echo "   Build Number: ${BUILD_NUMBER}"
echo ""
echo "📦 This will create:"
echo "   - AAB file for Google Play Store"
echo "   - APK file (for testing)"
echo "   - BIN file for Cafe Bazar"
echo "   - All files will be saved to: releases/v${VERSION}/"
echo ""

# Optional: Ask for confirmation (uncomment to enable)
# read -p "Continue with build? (y/n) " -n 1 -r
# echo
# if [[ ! $REPLY =~ ^[Yy]$ ]]; then
#     echo "Build cancelled."
#     exit 0
# fi

# Skip clean to preserve dependencies cache
# Uncomment the following lines if you want a clean build:
# echo "🧹 Cleaning previous builds..."
# flutter clean

# Verify dependencies are available
if [ ! -f "pubspec.lock" ]; then
    echo "❌ No pubspec.lock found. Please run 'flutter pub get' manually first."
    exit 1
fi

echo "📦 Using existing dependencies from pubspec.lock..."

# Build AAB (Android App Bundle)
echo "🏗️  Building AAB (Android App Bundle)..."
flutter build appbundle --release

# Check if AAB build was successful
if [ $? -eq 0 ]; then
    AAB_PATH="build/app/outputs/bundle/release/app-release.aab"
    if [ -f "$AAB_PATH" ]; then
        echo "✅ AAB build successful!"
        echo "📦 AAB location: $AAB_PATH"
        
        # Copy AAB to releases directory
        RELEASE_DIR="releases/v${VERSION}"
        mkdir -p "$RELEASE_DIR"
        cp "$AAB_PATH" "$RELEASE_DIR/app-release-v${VERSION}.aab"
        echo "📋 Copied to: $RELEASE_DIR/app-release-v${VERSION}.aab"
    else
        echo "❌ AAB file not found at expected location: $AAB_PATH"
        exit 1
    fi
else
    echo "❌ AAB build failed!"
    exit 1
fi

# Build APK for Cafe Bazar BIN file
echo ""
echo "🏗️  Building APK for Cafe Bazar..."
flutter build apk --release

# Check if APK build was successful
if [ $? -eq 0 ]; then
    APK_PATH="build/app/outputs/flutter-apk/app-release.apk"
    if [ -f "$APK_PATH" ]; then
        echo "✅ APK build successful!"
        echo "📱 APK location: $APK_PATH"
    else
        echo "❌ APK file not found at expected location: $APK_PATH"
        exit 1
    fi
else
    echo "❌ APK build failed!"
    exit 1
fi

# Create BIN file for Cafe Bazar using bundlesigner
echo ""
echo "🔐 Creating BIN file for Cafe Bazar..."

# Verify AAB file exists
if [ -z "$AAB_PATH" ] || [ ! -f "$AAB_PATH" ]; then
    echo "❌ AAB file not found. Cannot create BIN file."
    exit 1
fi

# Check if bundlesigner.jar exists
if [ ! -f "bundlesigner.jar" ]; then
    echo "❌ bundlesigner.jar not found!"
    echo "   Please download bundlesigner.jar from Cafe Bazar developer panel"
    exit 1
fi

# Check if keystore exists
KEYSTORE_PATH="android/upload-keystore.jks"
if [ ! -f "$KEYSTORE_PATH" ]; then
    echo "❌ Keystore not found at: $KEYSTORE_PATH"
    echo "   Please create a keystore file or update KEYSTORE_PATH in this script"
    exit 1
fi

# Read key properties
KEY_PROPERTIES="android/key.properties"
if [ ! -f "$KEY_PROPERTIES" ]; then
    echo "❌ key.properties not found at: $KEY_PROPERTIES"
    exit 1
fi

source "$KEY_PROPERTIES"

# Create BIN file using genbin command (requires AAB file)
BIN_OUTPUT_DIR="bin_files"
BIN_OUTPUT="$BIN_OUTPUT_DIR/app-release-v${VERSION}.bin"
mkdir -p "$BIN_OUTPUT_DIR"

echo "   Using AAB: $AAB_PATH"
echo "   Output BIN: $BIN_OUTPUT"

java -jar bundlesigner.jar genbin \
    -v \
    --bundle "$AAB_PATH" \
    --bin "$BIN_OUTPUT_DIR" \
    --v2-signing-enabled true \
    --v3-signing-enabled false \
    --ks "$KEYSTORE_PATH" \
    --ks-key-alias "$keyAlias" \
    --ks-pass "pass:$storePassword" \
    --key-pass "pass:$keyPassword"

# Find the generated BIN file (bundlesigner may name it differently)
# It should be in the BIN_OUTPUT_DIR
GENERATED_BIN=$(find "$BIN_OUTPUT_DIR" -name "*.bin" -type f -newer "$AAB_PATH" | head -1)

if [ -z "$GENERATED_BIN" ]; then
    # Try to find any .bin file in the directory
    GENERATED_BIN=$(ls -t "$BIN_OUTPUT_DIR"/*.bin 2>/dev/null | head -1)
fi

if [ -n "$GENERATED_BIN" ] && [ -f "$GENERATED_BIN" ]; then
    # Rename to our desired name
    mv "$GENERATED_BIN" "$BIN_OUTPUT" 2>/dev/null || cp "$GENERATED_BIN" "$BIN_OUTPUT"
    
    echo "✅ BIN file created successfully!"
    echo "📦 BIN location: $BIN_OUTPUT"
    
    # Copy to releases directory
    cp "$BIN_OUTPUT" "$RELEASE_DIR/app-release-v${VERSION}.bin"
    echo "📋 Copied to: $RELEASE_DIR/app-release-v${VERSION}.bin"
    
    # Show file size
    BIN_SIZE=$(du -h "$BIN_OUTPUT" | cut -f1)
    echo "📊 BIN file size: $BIN_SIZE"
else
    echo "❌ BIN file creation failed! Check the output above for errors."
    echo "   Expected location: $BIN_OUTPUT"
    exit 1
fi

echo ""
echo "============================================="
echo "✅ Build completed successfully!"
echo ""
echo "📦 Release files:"
echo "   - AAB: $RELEASE_DIR/app-release-v${VERSION}.aab"
echo "   - BIN: $RELEASE_DIR/app-release-v${VERSION}.bin"
echo ""
echo "⚠️  Next steps:"
echo "   1. Test the BIN file on a real device"
echo "   2. Upload BIN to Cafe Bazar developer panel"
echo "   3. Upload AAB to Google Play Console (if applicable)"
echo ""

