#!/bin/bash

# Watch and auto-deploy script for NailedIt theme
# Monitors file changes and automatically syncs to VPS

# Configuration
REMOTE_USER="root"
REMOTE_HOST="45.93.139.96"
REMOTE_PATH="/var/www/html/nailedit/wp-content/themes/nailedit"
LOCAL_PATH="$(cd "$(dirname "$0")" && pwd)"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🔍 Starting file watcher...${NC}"
echo "Monitoring: $LOCAL_PATH"
echo "Remote: $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH"
echo -e "${YELLOW}Press Ctrl+C to stop${NC}"
echo ""

# Function to deploy
deploy() {
  echo -e "${YELLOW}📦 Deploying changes...${NC}"
  
  rsync -azh \
    --delete \
    --exclude 'node_modules/' \
    --exclude '.git/' \
    --exclude '.DS_Store' \
    --exclude 'deploy.sh' \
    --exclude 'watch-deploy.sh' \
    --exclude '.gitignore' \
    --exclude '*.log' \
    "$LOCAL_PATH/" \
    "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/" \
    2>&1 | grep -v "building file list"
  
  if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Deployed at $(date '+%H:%M:%S')${NC}"
  else
    echo -e "${RED}✗ Deployment failed${NC}"
  fi
  echo ""
}

# Initial deployment
deploy

# Watch for changes using fswatch (macOS)
if command -v fswatch &> /dev/null; then
  fswatch -o \
    --exclude 'node_modules' \
    --exclude '.git' \
    --exclude '.DS_Store' \
    --exclude '*.log' \
    --exclude 'deploy.sh' \
    --exclude 'watch-deploy.sh' \
    "$LOCAL_PATH" | while read change; do
    deploy
  done
else
  echo -e "${YELLOW}⚠️  fswatch not found. Installing...${NC}"
  echo "Run: brew install fswatch"
  echo ""
  echo "Alternative: Use 'npm run deploy' manually after changes"
fi
