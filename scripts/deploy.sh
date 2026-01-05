#!/bin/bash

# ==============================================================================
# Deployment Script for Hostpoint
# ==============================================================================

REMOTE_USER="blauesk2"
REMOTE_HOST="sl1070.web.hostpoint.ch"
REMOTE_PATH="/home/blauesk2/www/zivi-spesenrechner.blaueskreuz.online"

echo "Starting deployment to $REMOTE_HOST..."

rsync -avz --delete \
  --exclude='.git/' \
  --exclude='.ddev/' \
  --exclude='.vscode/' \
  --exclude='web/sites/default/files/' \
  --exclude='web/sites/default/settings.php' \
  --exclude='web/sites/default/settings.local.php' \
  --exclude='node_modules/' \
  ./ ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}

echo "Fixing permissions on server..."
ssh ${REMOTE_USER}@${REMOTE_HOST} "
  cd ${REMOTE_PATH} && \
  find . -type d -exec chmod 755 {} + && \
  find . -type f -exec chmod 644 {} + && \
  chmod +x vendor/bin/drush web/core/scripts/*.sh
"

echo "Deployment complete!"