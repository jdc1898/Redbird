#!/bin/bash

# Manual composer version bump script
# Usage: ./scripts/bump-composer-version.sh [patch|minor|major]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    print_error "Not in a git repository"
    exit 1
fi

# Get current version from composer.json
CURRENT_VERSION=$(composer config version)
print_status "Current version: $CURRENT_VERSION"

# Determine version bump type
BUMP_TYPE=${1:-patch}

if [[ ! "$BUMP_TYPE" =~ ^(patch|minor|major)$ ]]; then
    print_error "Invalid bump type. Use: patch, minor, or major"
    exit 1
fi

# Calculate new version
IFS='.' read -ra VERSION_PARTS <<< "$CURRENT_VERSION"
MAJOR=${VERSION_PARTS[0]}
MINOR=${VERSION_PARTS[1]}
PATCH=${VERSION_PARTS[2]}

case $BUMP_TYPE in
    patch)
        NEW_PATCH=$((PATCH + 1))
        NEW_VERSION="$MAJOR.$MINOR.$NEW_PATCH"
        ;;
    minor)
        NEW_MINOR=$((MINOR + 1))
        NEW_VERSION="$MAJOR.$NEW_MINOR.0"
        ;;
    major)
        NEW_MAJOR=$((MAJOR + 1))
        NEW_VERSION="$NEW_MAJOR.0.0"
        ;;
esac

print_status "New version: $NEW_VERSION"

# Confirm the bump
read -p "Do you want to bump composer.json version to $NEW_VERSION? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Version bump cancelled"
    exit 0
fi

# Update composer.json version
print_status "Updating composer.json version..."
composer config version "$NEW_VERSION"

# Commit the version change
print_status "Committing version change..."
git add composer.json
git commit -m "Bump version to $NEW_VERSION"

print_status "Version bumped to $NEW_VERSION successfully!"
print_status "Now push to main to trigger auto-release:"
print_status "  git push origin main"
