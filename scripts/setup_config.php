<?php

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

// 1. Create Paragraph Type
if (!ParagraphsType::load('expense_line_item')) {
  ParagraphsType::create([
    'id' => 'expense_line_item',
    'label' => 'Expense Line Item',
  ])->save();
  echo "Created Paragraph Type: expense_line_item\n";
}

// 2. Create Content Type
if (!NodeType::load('spesenabrechnung')) {
  NodeType::create([
    'type' => 'spesenabrechnung',
    'name' => 'Spesenabrechnung',
  ])->save();
  echo "Created Content Type: spesenabrechnung\n";
}

// Helper to create field
function create_field($entity_type, $bundle, $field_name, $field_type, $label, $storage_settings = [], $instance_settings = []) {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => $field_type,
      'settings' => $storage_settings,
    ])->save();
    echo "Created Storage: $field_name\n";
  }

  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $label,
      'settings' => $instance_settings,
    ])->save();
    echo "Created Instance: $field_name on $bundle\n";
  }
}

// 3. Fields for Expense Line Item
create_field('paragraph', 'expense_line_item', 'field_item_type', 'list_string', 'Item Type', [
  'allowed_values' => [
    'Taschengeld' => 'Taschengeld',
    'Morgenessen' => 'Morgenessen',
    'Mittagessen' => 'Mittagessen',
    'Nachtessen' => 'Nachtessen',
    'OV/Sonstiges' => 'OV/Sonstiges',
  ],
]);
create_field('paragraph', 'expense_line_item', 'field_rate', 'decimal', 'Rate');
create_field('paragraph', 'expense_line_item', 'field_quantity', 'decimal', 'Quantity');
create_field('paragraph', 'expense_line_item', 'field_total_amount', 'decimal', 'Total Amount');
create_field('paragraph', 'expense_line_item', 'field_receipt', 'file', 'Receipt', [], ['file_extensions' => 'pdf jpg png']);
create_field('paragraph', 'expense_line_item', 'field_description', 'string', 'Description');
create_field('paragraph', 'expense_line_item', 'field_comment', 'string_long', 'Comment');

// 4. Fields for Spesenabrechnung
create_field('node', 'spesenabrechnung', 'field_date_range', 'daterange', 'Date Range');
create_field('node', 'spesenabrechnung', 'field_status', 'list_string', 'Status', [
  'allowed_values' => [
    'Draft' => 'Draft',
    'Submitted' => 'Submitted',
    'Approved' => 'Approved',
  ],
]);
create_field('node', 'spesenabrechnung', 'field_zivi_ref', 'entity_reference', 'Zivi', ['target_type' => 'user']);
create_field('node', 'spesenabrechnung', 'field_total_sum', 'decimal', 'Total Sum');
create_field('node', 'spesenabrechnung', 'field_editor_comment', 'string_long', 'Editor Comment');

// 5. Fields for User
create_field('user', 'user', 'field_iban', 'string', 'IBAN');
create_field('user', 'user', 'field_address', 'string_long', 'Address');
create_field('user', 'user', 'field_full_name', 'string', 'Full Name');
create_field('user', 'user', 'field_service_start', 'datetime', 'Erster Einsatztag', ['datetime_type' => 'date']);
create_field('user', 'user', 'field_service_end', 'datetime', 'Letzter Einsatztag', ['datetime_type' => 'date']);

// Entity Reference Revisions for Paragraphs
if (!FieldStorageConfig::loadByName('node', 'field_expense_items')) {
  FieldStorageConfig::create([
    'field_name' => 'field_expense_items',
    'entity_type' => 'node',
    'type' => 'entity_reference_revisions',
    'cardinality' => -1,
    'settings' => ['target_type' => 'paragraph'],
  ])->save();
  echo "Created Storage: field_expense_items (unlimited)\n";
} else {
  // Update existing storage if needed
  $storage = FieldStorageConfig::loadByName('node', 'field_expense_items');
  if ($storage->getCardinality() != -1) {
    $storage->setCardinality(-1);
    $storage->save();
    echo "Updated Storage: field_expense_items to unlimited cardinality\n";
  }
}
if (!FieldConfig::loadByName('node', 'spesenabrechnung', 'field_expense_items')) {
  FieldConfig::create([
    'field_name' => 'field_expense_items',
    'entity_type' => 'node',
    'bundle' => 'spesenabrechnung',
    'label' => 'Expense Items',
    'settings' => [
      'handler' => 'default:paragraph',
      'handler_settings' => ['target_bundles' => ['expense_line_item' => 'expense_line_item']],
    ],
  ])->save();
  echo "Created Instance: field_expense_items on spesenabrechnung\n";
}

// 5. Configure Form Displays
$entity_display_repository = \Drupal::service('entity_display.repository');

// Ensure 'pdf' view mode exists for nodes
use Drupal\Core\Entity\Entity\EntityViewMode;
if (!EntityViewMode::load('node.pdf')) {
  EntityViewMode::create([
    'id' => 'node.pdf',
    'label' => 'PDF',
    'targetEntityType' => 'node',
  ])->save();
  echo "Created View Mode: node.pdf\n";
}

// Enable 'pdf' view mode for spesenabrechnung
$view_display = $entity_display_repository->getViewDisplay('node', 'spesenabrechnung', 'pdf');
if ($view_display->isNew()) {
  $view_display->setStatus(TRUE)->save();
  echo "Enabled PDF view mode for spesenabrechnung\n";
}

// Form Display for Paragraph
$form_display = $entity_display_repository->getFormDisplay('paragraph', 'expense_line_item');
$form_display->setComponent('field_item_type', ['type' => 'options_select'])
  ->setComponent('field_rate', ['type' => 'number'])
  ->setComponent('field_quantity', ['type' => 'number'])
  ->setComponent('field_total_amount', ['type' => 'number'])
  ->setComponent('field_receipt', ['type' => 'file_generic'])
  ->setComponent('field_description', ['type' => 'string_textfield'])
  ->setComponent('field_comment', ['type' => 'string_textarea'])
  ->save();

// Form Display for Node
$form_display = $entity_display_repository->getFormDisplay('node', 'spesenabrechnung');
$form_display->setComponent('field_date_range', ['type' => 'daterange_default'])
  ->setComponent('field_zivi_ref', ['type' => 'entity_reference_autocomplete'])
  ->setComponent('field_status', ['type' => 'options_select'])
  ->setComponent('field_expense_items', ['type' => 'entity_reference_paragraphs'])
  ->setComponent('field_editor_comment', ['type' => 'string_textarea'])
  ->removeComponent('field_total_sum') // Hide total sum from form, calculated automatically
  ->save();

// Form Display for User
$form_display = $entity_display_repository->getFormDisplay('user', 'user');
$form_display->setComponent('field_iban', ['type' => 'string_textfield'])
  ->setComponent('field_address', ['type' => 'string_textarea'])
  ->setComponent('field_service_start', ['type' => 'datetime_default'])
  ->setComponent('field_service_end', ['type' => 'datetime_default'])
  ->save();

// 6. Create Roles and Permissions
use Drupal\user\Entity\Role;

if (!Role::load('zivi')) {
  $role = Role::create([
    'id' => 'zivi',
    'label' => 'Zivi',
  ]);
  $role->save();
  echo "Created Role: zivi\n";

  // Grant permissions
  user_role_grant_permissions('zivi', [
    'create spesenabrechnung content',
    'edit own spesenabrechnung content',
    'delete own spesenabrechnung content',
    'access content',
    'entity print access bundle spesenabrechnung',
    'entity print access type node',
  ]);
  echo "Granted permissions to zivi role.\n";
}

if (!Role::load('editor')) {
  $role = Role::create([
    'id' => 'editor',
    'label' => 'Editor',
  ]);
  $role->save();
  echo "Created Role: editor\n";

  // Grant permissions
  user_role_grant_permissions('editor', [
    'access content',
    'edit any spesenabrechnung content',
    'entity print access bundle spesenabrechnung',
    'entity print access type node',
  ]);
  echo "Granted permissions to editor role.\n";
}

// Ensure administrator has all permissions
user_role_grant_permissions('administrator', [
  'bypass entity print access',
  'administer entity print',
]);

echo "Configuration Complete.\n";
