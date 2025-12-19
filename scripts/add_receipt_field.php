<?php

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

// 1. Add 'field_receipt' to 'expense_line_item' paragraph type.
if (!FieldStorageConfig::loadByName('paragraph', 'field_receipt')) {
  FieldStorageConfig::create([
    'field_name' => 'field_receipt',
    'entity_type' => 'paragraph',
    'type' => 'file',
    'settings' => [
      'uri_scheme' => 'public',
      'target_type' => 'file',
      'display_field' => TRUE,
      'display_default' => TRUE,
    ],
  ])->save();
}

if (!FieldConfig::loadByName('paragraph', 'expense_line_item', 'field_receipt')) {
  FieldConfig::create([
    'field_name' => 'field_receipt',
    'entity_type' => 'paragraph',
    'bundle' => 'expense_line_item',
    'label' => 'Receipt',
    'settings' => [
      'file_extensions' => 'jpg jpeg png pdf',
      'file_directory' => 'receipts/[date:custom:Y]-[date:custom:m]',
      'max_filesize' => '5MB',
      'description_field' => FALSE,
    ],
  ])->save();
}

echo "Field 'field_receipt' created successfully.\n";
