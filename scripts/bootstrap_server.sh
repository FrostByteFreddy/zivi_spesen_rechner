#!/bin/bash

# ==============================================================================
# Drupal Bootstrap Script for Hostpoint
# ==============================================================================

# Define Drush command
# On Hostpoint, we use 'php' (which we verified is 8.3) and point to the actual PHP file
DRUSH="php ./vendor/drush/drush/drush.php"

echo "Checking PHP version..."
php -v

# Ensure drush is executable
chmod +x ./vendor/bin/drush

# 1. Database Configuration (Optional)
# If DB_NAME is provided, we attempt a site install
if [ ! -z "$DB_NAME" ] && [ ! -z "$DB_USER" ] && [ ! -z "$DB_PASS" ]; then
  DB_HOST=${DB_HOST:-blauesk2.mysql.db.internal}
  echo "Installing Drupal with database: $DB_NAME on $DB_HOST..."
  $DRUSH si --db-url="mysql://${DB_USER}:${DB_PASS}@${DB_HOST}/${DB_NAME}" -y
else
  echo "Skipping site install (DB credentials not provided)."
fi

# 2. Enable Modules
echo "Enabling modules..."
$DRUSH en zivi_spesen entity_print paragraphs -y

# 4. Run Custom Configuration Script
echo "Running custom configuration..."
$DRUSH scr scripts/setup_config.php

# 5. Create/Update the requested user
ZIVI_USER="zivi"
ZIVI_PASS="tA51d3'[KU%("
echo "Creating/Updating zivi user $ZIVI_USER..."
$DRUSH user:create $ZIVI_USER --mail="zivi@blaueskreuz.ch" --password="$ZIVI_PASS" || true
$DRUSH user:role:add zivi $ZIVI_USER || true

# 6. Create/Update Editor User
EDITOR_USER="editor"
EDITOR_PASS="Ed1t0r_Sp3s3n!2025"
echo "Creating/Updating editor user $EDITOR_USER..."
$DRUSH user:create $EDITOR_USER --mail="editor@blaueskreuz.ch" --password="$EDITOR_PASS" || true
$DRUSH user:role:add editor $EDITOR_USER || true

# 7. Create/Update Admin User
ADMIN_USER="informatik@blaueskreuz.ch"
ADMIN_PASS="c'5i&4a0B£*z^tWV/&mld9VYqO'i,+'"
echo "Creating/Updating admin user $ADMIN_USER..."
$DRUSH user:create $ADMIN_USER --mail="informatik@blaueskreuz.ch" --password="$ADMIN_PASS" || true
$DRUSH user:role:add administrator $ADMIN_USER || true

# 6. Final Cache Clear
echo "Final cache clear..."
$DRUSH cr

echo "=============================================================================="
echo "Bootstrap Complete!"
echo "Site: https://zivi-spesenrechner.blaueskreuz.online"
echo "Editor User: $EDITOR_USER"
echo "Editor Pass: $EDITOR_PASS"
echo "Admin User: $ADMIN_USER"
echo "Admin Pass: $ADMIN_PASS"
echo "=============================================================================="
