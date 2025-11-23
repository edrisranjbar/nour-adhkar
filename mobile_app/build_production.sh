#!/bin/bash
# Production build helper
# Cleans the workspace, installs dependencies, and delegates to build_release.sh
# so that AAB/APK/BIN artifacts end up inside releases/vX.Y.Z automatically.

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="${SCRIPT_DIR}"

echo "🔨 Preparing Nour Adhkar - Production Build"
echo "============================================="

cd "$PROJECT_ROOT"

echo "🧹 Cleaning previous builds..."
flutter clean

echo "📦 Getting dependencies..."
flutter pub get

echo "🚀 Creating production release artifacts (AAB/APK/BIN)..."
bash "$PROJECT_ROOT/build_release.sh"

echo ""
echo "✅ Production build finished. Release files are inside releases/."

