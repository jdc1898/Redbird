#!/bin/bash

# Simple release script - just commit and push to trigger auto-release
# Usage: ./scripts/release.sh [commit_message]

set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Check if we're in a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Not in a git repository"
    exit 1
fi

# Check if we have changes to commit
if git diff-index --quiet HEAD --; then
    print_warning "No changes to commit. Everything is up to date."
    exit 0
fi

# Get commit message
COMMIT_MESSAGE=${1:-"Auto-release: $(date +%Y-%m-%d)"}

print_status "Preparing release..."

# Add all changes
git add .

# Commit with message
git commit -m "$COMMIT_MESSAGE"

# Push to main (this triggers the auto-release workflow)
print_status "Pushing to main to trigger auto-release..."
git push origin main

print_status "✅ Release triggered!"
print_status "GitHub Actions will automatically:"
print_status "  - Run tests"
print_status "  - Bump version"
print_status "  - Create tag"
print_status "  - Create GitHub release"
print_status "  - Update Packagist (if configured)"

print_status "Check the workflow at: https://github.com/$(git config --get remote.origin.url | sed 's/.*github.com[:/]\([^/]*\/[^/]*\).*/\1/')/actions"
