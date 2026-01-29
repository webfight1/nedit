#!/bin/bash

# Deployment script for NailedIt theme
# Syncs local theme files to VPS server

# Configuration
REMOTE_USER="root"
REMOTE_HOST="45.93.139.96"
REMOTE_PATH="/var/www/html/nailedit/wp-content/themes/nailedit"
LOCAL_PATH="$(cd "$(dirname "$0")" && pwd)"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Starting deployment to VPS...${NC}"
echo "Local path: $LOCAL_PATH"
echo "Remote: $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH"
echo ""

# Rsync options:
# -a: archive mode (preserves permissions, timestamps, etc.)
# -v: verbose
# -z: compress during transfer
# -h: human-readable output
# --delete: delete files on remote that don't exist locally
# --exclude: exclude certain files/folders

rsync -avzh \
  --delete \
  --exclude 'node_modules/' \
  --exclude '.git/' \
  --exclude '.DS_Store' \
  --exclude 'deploy.sh' \
  --exclude '.gitignore' \
  --exclude '*.log' \
  "$LOCAL_PATH/" \
  "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/"

if [ $? -eq 0 ]; then
  echo ""
  echo -e "${GREEN}✓ Deployment completed successfully!${NC}"
else
  echo ""
  echo -e "${RED}✗ Deployment failed!${NC}"
  exit 1
fi
