#!/bin/bash

# ==============================================================================
# Local Development Reset Script (DDEV)
# Mirrors the logic of bootstrap_server.sh but for local environment
# ==============================================================================

echo "Starting local reset..."

# Load Environment Variables
if [ -f .ddev/.env ]; then
  source .ddev/.env
else
  echo "Error: .ddev/.env file not found. Please create it with the required variables."
  exit 1
fi

# 1. Site Install (Resets Database)
echo "Re-installing Drupal..."
ddev drush si -y

# 2. Enable Modules
echo "Enabling modules..."
ddev drush en zivi_spesen entity_print paragraphs -y

# 3. Run Custom Configuration Script
echo "Running custom configuration..."
ddev drush scr scripts/setup_config.php

# 4. Create/Update Zivi User
echo "Creating/Updating zivi user $ZIVI_USER..."
ddev drush user:create $ZIVI_USER --mail="$ZIVI_EMAIL" --password="$ZIVI_PASS" || true
ddev drush user:role:add zivi $ZIVI_USER || true

# 5. Create/Update Editor User
echo "Creating/Updating editor user $EDITOR_USER..."
ddev drush user:create $EDITOR_USER --mail="$EDITOR_EMAIL" --password="$EDITOR_PASS" || true
ddev drush user:role:add editor $EDITOR_USER || true

# 6. Create/Update Admin User
echo "Creating/Updating admin user $ADMIN_USER..."
ddev drush user:create $ADMIN_USER --mail="$ADMIN_EMAIL" --password="$ADMIN_PASS" || true
ddev drush user:role:add administrator $ADMIN_USER || true

# 7. Final Cache Clear
echo "Final cache clear..."
ddev drush cr

echo "=============================================================================="
echo "Reset Complete!"
echo "Local Site: https://zivi-spesen-rechner.ddev.site"
echo "Editor User: $EDITOR_USER"
echo "Editor Pass: $EDITOR_PASS"
echo "Admin User: $ADMIN_USER"
echo "Admin Pass: $ADMIN_PASS"
echo "=============================================================================="