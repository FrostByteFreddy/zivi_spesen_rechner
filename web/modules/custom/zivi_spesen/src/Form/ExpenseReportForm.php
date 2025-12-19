<?php

namespace Drupal\zivi_spesen\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form for creating a new expense report.
 */
class ExpenseReportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'zivi_spesen_expense_report_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Node $node = NULL) {
    $form['#theme'] = 'expense_report_form';
    $form['#attached']['library'][] = 'zivi_spesen/calculator';
    $form['#attached']['library'][] = 'zivi_spesen/app_styling';
    $form['#cache']['max-age'] = 0;
    
    // Store node in form state for submit handler
    if ($node) {
      $form_state->set('node', $node);
    }

    // 1. Header / Date Range
    $form['header'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['expense-form-header']],
    ];

    $start_date = new DrupalDateTime('first day of this month');
    $end_date = new DrupalDateTime('last day of this month');
    
    // Pre-fill dates if editing
    if ($node) {
      $date_range = $node->get('field_date_range')->first();
      if ($date_range) {
        $start_date = DrupalDateTime::createFromFormat('Y-m-d', $date_range->getValue()['value']);
        $end_date = DrupalDateTime::createFromFormat('Y-m-d', $date_range->getValue()['end_value']);
      }
    }

    $form['header']['date_range_start'] = [
      '#type' => 'date',
      '#title' => $this->t('Start Date'),
      '#default_value' => $start_date->format('Y-m-d'),
      '#attributes' => ['class' => ['expense-date-start']],
    ];

    $form['header']['date_range_end'] = [
      '#type' => 'date',
      '#title' => $this->t('End Date'),
      '#default_value' => $end_date->format('Y-m-d'),
      '#attributes' => ['class' => ['expense-date-end']],
    ];

    // 2. Expense Items Wrapper
    $form['expenses'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['expense-grid-wrapper']],
      '#tree' => TRUE, // Important for values structure
    ];

    // Prepare existing data for lookup
    $existing_data = [];
    $custom_rows_data = [];
    if ($node) {
      foreach ($node->get('field_expense_items')->referencedEntities() as $paragraph) {
        $type = $paragraph->get('field_item_type')->value;
        $rate = $paragraph->get('field_rate')->value;
        $quantity = $paragraph->get('field_quantity')->value;
        $total = $paragraph->get('field_total_amount')->value;
        
        $receipt_fid = null;
        if ($paragraph->hasField('field_receipt') && !$paragraph->get('field_receipt')->isEmpty()) {
          $receipt_fid = $paragraph->get('field_receipt')->target_id;
        }

        $data = [
          'rate' => $rate,
          'quantity' => $quantity,
          'total' => $total,
          'receipt' => $receipt_fid,
          'type' => $type,
        ];

        // Check if standard
        $standard_keys = ['Taschengeld', 'Morgenessen', 'Mittagessen', 'Nachtessen'];
        if (in_array($type, $standard_keys)) {
          $existing_data[$type] = $data;
        } else {
          $custom_rows_data[] = $data;
        }
      }
    }

    // Standard Items
    $standard_items = [
      'Taschengeld' => 7.50,
      'Morgenessen' => 4.00,
      'Mittagessen' => 9.00,
      'Nachtessen' => 7.00,
    ];

    foreach ($standard_items as $label => $rate) {
      $key = strtolower($label); // simple key
      
      // Get existing values
      $default_qty = 0;
      $default_total = '0.00';
      if (isset($existing_data[$label])) {
        $default_qty = $existing_data[$label]['quantity'];
        $default_total = $existing_data[$label]['total'];
      }

      // Row Container
      $form['expenses'][$key] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['grid', 'grid-cols-12', 'gap-4', 'items-center', 'border-b', 'border-gray-100']],
      ];

      // Cell 1: Item (3 cols)
      $form['expenses'][$key]['item_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-3', 'font-medium', 'text-gray-900']],
        'label' => ['#markup' => $label],
        'type_hidden' => [
          '#type' => 'hidden',
          '#value' => $label,
          '#attributes' => ['class' => ['expense-type']],
        ],
      ];

      // Cell 2: Rate (2 cols)
      $form['expenses'][$key]['rate_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-2', 'text-gray-600']],
        'display' => ['#markup' => number_format($rate, 2)],
        'rate_hidden' => [
          '#type' => 'hidden',
          '#value' => $rate,
          '#attributes' => ['class' => ['expense-rate']],
        ],
      ];

      // Cell 3: Quantity (2 cols)
      $form['expenses'][$key]['quantity_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-2']],
        'quantity' => [
          '#type' => 'number',
          '#title' => $this->t('Days'),
          '#title_display' => 'invisible',
          '#default_value' => $default_qty,
          '#attributes' => ['class' => ['expense-quantity', 'w-full', 'rounded-md', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'], 'min' => 0],
        ],
      ];

      // Cell 4: Total (2 cols)
      $form['expenses'][$key]['total_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-2']],
        'total' => [
          '#type' => 'textfield',
          '#title' => $this->t('Total'),
          '#title_display' => 'invisible',
          '#default_value' => $default_total,
          '#attributes' => ['class' => ['expense-total', 'w-full', 'rounded-md', 'border-gray-300', 'bg-gray-50', 'text-right'], 'readonly' => 'readonly'],
        ],
      ];
      
      // Cell 5: Receipt (2 cols) - Empty
      $form['expenses'][$key]['receipt_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-2']],
      ];

      // Cell 6: Actions (1 col) - Empty
      $form['expenses'][$key]['actions_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-1']],
      ];
    }

    // Custom Rows (e.g. OV/Sonstiges)
    // Dynamic rows using AJAX
    
    // Initialize row count
    if (!$form_state->has('custom_row_count')) {
      $initial_count = count($custom_rows_data);
      $form_state->set('custom_row_count', $initial_count);
    }
    
    $custom_row_count = $form_state->get('custom_row_count');

    // Container for AJAX replacement
    $form['expenses']['#prefix'] = '<div id="expenses-table-wrapper">';
    $form['expenses']['#suffix'] = '</div>';

    for ($i = 0; $i < $custom_row_count; $i++) {
      $key = 'custom_' . ($i + 1);
      
      $default_desc = '';
      $default_rate = '';
      $default_qty = '';
      $default_total = '';
      $default_receipt = [];

      if (isset($custom_rows_data[$i])) {
        $default_desc = $custom_rows_data[$i]['type'];
        $default_rate = $custom_rows_data[$i]['rate'];
        $default_qty = $custom_rows_data[$i]['quantity'];
        $default_total = $custom_rows_data[$i]['total'];
        if ($custom_rows_data[$i]['receipt']) {
          $default_receipt = [$custom_rows_data[$i]['receipt']];
        }
      }
      
      // Row Container
      $form['expenses'][$key] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['grid', 'grid-cols-12', 'gap-4', 'items-center', 'py-3', 'border-b', 'border-gray-100']],
      ];

      // Cell 1: Item Input (3 cols)
      $form['expenses'][$key]['item_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-3']],
        'item' => [
          '#type' => 'textfield',
          '#title' => $this->t('Description'),
          '#title_display' => 'invisible',
          '#default_value' => $default_desc,
          '#attributes' => ['class' => ['expense-type-input', 'w-full', 'rounded-md', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'], 'placeholder' => 'Description'],
        ],
      ];
      
      // Cell 2: Rate Input (2 cols)
      $form['expenses'][$key]['rate_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-2']],
        'rate_col' => [
          '#type' => 'number',
          '#step' => '0.05',
          '#title' => $this->t('Rate'),
          '#title_display' => 'invisible',
          '#default_value' => $default_rate,
          '#attributes' => ['class' => ['expense-rate-input', 'w-full', 'rounded-md', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'], 'placeholder' => 'Rate'],
        ],
      ];

      // Cell 3: Quantity (2 cols)
      $form['expenses'][$key]['quantity_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-2']],
        'quantity' => [
          '#type' => 'number',
          '#title' => $this->t('Quantity'),
          '#title_display' => 'invisible',
          '#default_value' => $default_qty,
          '#attributes' => ['class' => ['expense-quantity', 'w-full', 'rounded-md', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'], 'placeholder' => 'Qty'],
        ],
      ];

      // Cell 4: Total (2 cols)
      $form['expenses'][$key]['total_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-2']],
        'total' => [
          '#type' => 'textfield',
          '#title' => $this->t('Total'),
          '#title_display' => 'invisible',
          '#default_value' => $default_total,
          '#attributes' => ['class' => ['expense-total', 'w-full', 'rounded-md', 'border-gray-300', 'bg-gray-50', 'text-right'], 'readonly' => 'readonly', 'placeholder' => '0.00'],
        ],
      ];
      
      // Cell 5: Receipt (2 cols)
      $form['expenses'][$key]['receipt_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-2']],
        'receipt' => [
          '#type' => 'managed_file',
          '#title' => $this->t('Receipt'),
          '#title_display' => 'invisible',
          '#default_value' => $default_receipt,
          '#upload_location' => 'public://receipts/',
          '#upload_validators' => [
            'file_validate_extensions' => ['jpg jpeg png pdf'],
          ],
          '#attributes' => ['class' => ['expense-receipt', 'text-xs']],
        ],
      ];

      // Cell 6: Actions (1 col)
      $form['expenses'][$key]['actions_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-span-1', 'text-center']],
        'remove' => [
          '#type' => 'submit',
          '#value' => 'Remove',
          '#name' => 'remove_row_' . ($i + 1),
          '#submit' => ['::removeRow'],
          '#ajax' => [
            'callback' => '::addmoreCallback',
            'wrapper' => 'expenses-table-wrapper',
          ],
          '#attributes' => ['class' => ['text-xs', 'text-red-700', 'bg-red-100', 'hover:bg-red-200', 'border', 'border-transparent', 'rounded', 'px-2', 'py-1', 'cursor-pointer']],
          '#limit_validation_errors' => [],
        ],
      ];
    }

    // Add Row Button
    $form['add_row'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Expense'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'expenses-table-wrapper',
      ],
      '#attributes' => ['class' => ['inline-block', 'px-4', 'py-2', 'border', 'border-transparent', 'text-sm', 'font-medium', 'rounded-md', 'text-indigo-700', 'bg-indigo-100', 'hover:bg-indigo-200', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-indigo-500', 'cursor-pointer']],
      '#limit_validation_errors' => [], // Don't validate required fields when adding row
    ];

    // 3. Total Sum
    $total_sum_default = '0.00';
    if ($node) {
      $total_sum_default = $node->get('field_total_sum')->value;
    }

    $form['total_sum_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['total-sum-wrapper']],
    ];
    
    $form['total_sum_wrapper']['total_label'] = [
      '#markup' => '<label>Total Geldleistung zugunsten Zivildienstleistender:</label>',
    ];

    $form['total_sum_wrapper']['total_sum'] = [
      '#type' => 'textfield',
      '#default_value' => $total_sum_default,
      '#attributes' => ['class' => ['global-total-sum'], 'readonly' => 'readonly'],
    ];

    // 4. Actions
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Report'),
      '#button_type' => 'primary',
      '#attributes' => ['class' => ['w-full', 'block', 'text-center', 'py-2', 'px-4', 'border', 'border-transparent', 'rounded-md', 'shadow-sm', 'text-sm', 'font-medium', 'text-white', 'bg-indigo-600', 'hover:bg-indigo-700', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-indigo-500', 'cursor-pointer']],
    ];

    return $form;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['expenses'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $count = $form_state->get('custom_row_count');
    $count++;
    $form_state->set('custom_row_count', $count);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove-row" button.
   */
  public function removeRow(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $name = $triggering_element['#name']; // e.g., remove_row_2
    
    if (preg_match('/remove_row_(\d+)/', $name, $matches)) {
      $index_to_remove = intval($matches[1]); // 1-based index
      
      // Get current input
      $input = $form_state->getUserInput();
      
      // Shift values
      $count = $form_state->get('custom_row_count');
      
      // Loop from the removed index up to count-1
      for ($i = $index_to_remove; $i < $count; $i++) {
        $current_key = 'custom_' . $i;
        $next_key = 'custom_' . ($i + 1);
        
        if (isset($input['expenses'][$next_key])) {
          $input['expenses'][$current_key] = $input['expenses'][$next_key];
        }
      }
      
      // Remove the last one
      $last_key = 'custom_' . $count;
      unset($input['expenses'][$last_key]);
      
      // Update input and count
      $form_state->setUserInput($input);
      $form_state->set('custom_row_count', $count - 1);
      $form_state->setRebuild();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $user = \Drupal::currentUser();
    $node = $form_state->get('node');

    if ($node) {
      // Update existing node
      $node->setTitle('Spesenabrechnung ' . date('F Y', strtotime($values['date_range_start'])));
      $node->set('field_date_range', [
        'value' => $values['date_range_start'],
        'end_value' => $values['date_range_end'],
      ]);
      $node->set('field_total_sum', $values['total_sum']);
      
      // Clear existing paragraphs to rebuild them
      // This is simpler than trying to match and update
      $node->set('field_expense_items', []);
    } else {
      // Create Node
      $node = Node::create([
        'type' => 'spesenabrechnung',
        'title' => 'Spesenabrechnung ' . date('F Y', strtotime($values['date_range_start'])),
        'uid' => $user->id(),
        'field_date_range' => [
          'value' => $values['date_range_start'],
          'end_value' => $values['date_range_end'],
        ],
        'field_total_sum' => $values['total_sum'],
      ]);
    }

    // Create Paragraphs
    $expenses = $values['expenses']; // This is the table array
    
    $items_to_save = [];
    foreach ($expenses as $key => $row) {
      // Determine Type
      $type = '';
      
      // Check type_hidden (Standard)
      if (isset($row['type_hidden']) && !empty($row['type_hidden'])) {
        $type = $row['type_hidden'];
      } elseif (isset($row['item_wrapper']['type_hidden']) && !empty($row['item_wrapper']['type_hidden'])) {
        $type = $row['item_wrapper']['type_hidden'];
      }
      
      // Check item (Custom)
      if (empty($type)) {
        if (isset($row['item']) && !empty($row['item'])) {
          $type = $row['item'];
        } elseif (isset($row['item_wrapper']['item']) && !empty($row['item_wrapper']['item'])) {
          $type = $row['item_wrapper']['item'];
        }
      }

      // Skip empty rows
      if (empty($type)) {
        continue;
      }

      // Determine Rate
      $rate = 0;
      if (isset($row['rate_hidden']) && $row['rate_hidden'] !== '') {
        $rate = $row['rate_hidden'];
      } elseif (isset($row['rate_col']) && $row['rate_col'] !== '') {
        $rate = $row['rate_col'];
      } elseif (isset($row['rate_wrapper']['rate_hidden']) && $row['rate_wrapper']['rate_hidden'] !== '') {
        $rate = $row['rate_wrapper']['rate_hidden'];
      } elseif (isset($row['rate_wrapper']['rate_col']) && $row['rate_wrapper']['rate_col'] !== '') {
        $rate = $row['rate_wrapper']['rate_col'];
      }

      // Quantity
      $quantity = 0;
      if (isset($row['quantity'])) {
        $quantity = $row['quantity'];
      } elseif (isset($row['quantity_wrapper']['quantity'])) {
        $quantity = $row['quantity_wrapper']['quantity'];
      }

      // Total
      $total = 0;
      if (isset($row['total'])) {
        $total = $row['total'];
      } elseif (isset($row['total_wrapper']['total'])) {
        $total = $row['total_wrapper']['total'];
      }
      
      // Handle Receipt
      $receipt_fid = null;
      $receipt_val = null;
      
      if (isset($row['receipt'])) {
        $receipt_val = $row['receipt'];
      } elseif (isset($row['receipt_wrapper']['receipt'])) {
        $receipt_val = $row['receipt_wrapper']['receipt'];
      }

      if (!empty($receipt_val)) {
        // Managed file returns an array of FIDs
        if (is_array($receipt_val) && !empty($receipt_val[0])) {
          $receipt_fid = $receipt_val[0];
          // Load file and set permanent
          $file = \Drupal\file\Entity\File::load($receipt_fid);
          if ($file) {
            $file->setPermanent();
            $file->save();
          }
        }
      }

      if ($quantity > 0 || $total > 0) {
        $paragraph_values = [
          'type' => 'expense_line_item',
          'field_item_type' => $type,
          'field_rate' => $rate,
          'field_quantity' => $quantity,
          'field_total_amount' => $total,
        ];
        
        if ($receipt_fid) {
          $paragraph_values['field_receipt'] = ['target_id' => $receipt_fid];
        }

        $paragraph = Paragraph::create($paragraph_values);
        $paragraph->save();
        $items_to_save[] = $paragraph;
      }
    }

    if ($node) {
      $node->setTitle('Spesenabrechnung ' . date('F Y', strtotime($values['date_range_start'])));
      $node->set('field_date_range', [
        'value' => $values['date_range_start'],
        'end_value' => $values['date_range_end'],
      ]);
      $node->set('field_total_sum', $values['total_sum']);
      $node->set('field_expense_items', $items_to_save);
      $node->save();
    }
    
    $this->messenger()->addStatus($this->t('Expense report saved successfully.'));
    $form_state->setRedirect('zivi_spesen.dashboard');
  }

}
