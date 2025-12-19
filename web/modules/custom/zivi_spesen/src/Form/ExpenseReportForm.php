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

      // Cell 1: Item
      $form['expenses'][$key]['item'] = [
        'wrapper' => [
          '#type' => 'container',
          'label' => ['#markup' => '<strong>' . $label . '</strong>'],
          'type_hidden' => [
            '#type' => 'hidden',
            '#value' => $label,
            '#attributes' => ['class' => ['expense-type']],
          ],
        ],
      ];

      // Cell 2: Rate
      $form['expenses'][$key]['rate_col'] = [
        'wrapper' => [
          '#type' => 'container',
          'display' => ['#markup' => number_format($rate, 2)],
          'rate_hidden' => [
            '#type' => 'hidden',
            '#value' => $rate,
            '#attributes' => ['class' => ['expense-rate']],
          ],
        ],
      ];

      // Cell 3: Quantity
      $form['expenses'][$key]['quantity'] = [
        '#type' => 'number',
        '#title' => $this->t('Days'),
        '#title_display' => 'invisible',
        '#default_value' => $default_qty,
        '#attributes' => ['class' => ['expense-quantity'], 'min' => 0],
      ];

      // Cell 4: Total
      $form['expenses'][$key]['total'] = [
        '#type' => 'textfield', // Textfield to allow decimals easily
        '#title' => $this->t('Total'),
        '#title_display' => 'invisible',
        '#default_value' => $default_total,
        '#attributes' => ['class' => ['expense-total'], 'readonly' => 'readonly'],
      ];
      
      // Cell 5: Receipt (Empty for standard)
      $form['expenses'][$key]['receipt'] = [
        '#markup' => '',
      ];
    }

    // Custom Rows (e.g. OV/Sonstiges)
    // Dynamic rows using AJAX
    
    // Initialize row count
    if (!$form_state->has('custom_row_count')) {
      $initial_count = count($custom_rows_data);
      // If creating new report, start with 0 custom rows (as requested: "The additional rows should not be displayed")
      // If editing, start with existing count.
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
      
      // Cell 1: Item Input
      $form['expenses'][$key]['item'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Description'),
        '#title_display' => 'invisible',
        '#default_value' => $default_desc,
        '#attributes' => ['class' => ['expense-type-input'], 'placeholder' => 'Description (e.g. Train Ticket)'],
      ];
      
      // Cell 2: Rate Input
      $form['expenses'][$key]['rate_col'] = [
        '#type' => 'number',
        '#step' => '0.05',
        '#title' => $this->t('Rate'),
        '#title_display' => 'invisible',
        '#default_value' => $default_rate,
        '#attributes' => ['class' => ['expense-rate-input'], 'placeholder' => 'Rate'],
      ];

      // Cell 3: Quantity
      $form['expenses'][$key]['quantity'] = [
        '#type' => 'number',
        '#title' => $this->t('Quantity'),
        '#title_display' => 'invisible',
        '#default_value' => $default_qty,
        '#attributes' => ['class' => ['expense-quantity'], 'placeholder' => 'Qty'],
      ];

      // Cell 4: Total
      $form['expenses'][$key]['total'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Total'),
        '#title_display' => 'invisible',
        '#default_value' => $default_total,
        '#attributes' => ['class' => ['expense-total'], 'readonly' => 'readonly', 'placeholder' => '0.00'],
      ];
      
      // Cell 5: Receipt
      $form['expenses'][$key]['receipt'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Receipt'),
        '#title_display' => 'invisible',
        '#default_value' => $default_receipt,
        '#upload_location' => 'public://receipts/',
        '#upload_validators' => [
          'file_validate_extensions' => ['jpg jpeg png pdf'],
        ],
        '#attributes' => ['class' => ['expense-receipt']],
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
      '#attributes' => ['class' => ['button', 'button--secondary', 'add-expense-button']],
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
    
    foreach ($expenses as $key => $row) {
      // Determine Type
      $type = '';
      // Standard Item: Nested in item[wrapper][type_hidden]
      if (isset($row['item']['wrapper']['type_hidden'])) {
        $type = $row['item']['wrapper']['type_hidden'];
      } 
      // Custom Item: Direct value in item
      elseif (isset($row['item']) && is_string($row['item']) && !empty($row['item'])) {
        $type = $row['item'];
      }

      // Skip empty custom rows
      if (empty($type)) {
        continue;
      }

      // Determine Rate
      $rate = 0;
      // Standard Item: Nested in rate_col[wrapper][rate_hidden]
      if (isset($row['rate_col']['wrapper']['rate_hidden'])) {
        $rate = $row['rate_col']['wrapper']['rate_hidden'];
      } 
      // Custom Item: Direct value in rate_col
      elseif (isset($row['rate_col']) && is_numeric($row['rate_col'])) {
        $rate = $row['rate_col'];
      }

      $quantity = isset($row['quantity']) ? $row['quantity'] : 0;
      $total = isset($row['total']) ? $row['total'] : 0;
      
      // Handle Receipt
      $receipt_fid = null;
      // Receipt is in 'receipt' column
      if (isset($row['receipt']) && !empty($row['receipt'])) {
        // Managed file returns an array of FIDs
        if (is_array($row['receipt']) && !empty($row['receipt'][0])) {
          $receipt_fid = $row['receipt'][0];
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
        $node->get('field_expense_items')->appendItem($paragraph);
      }
    }

    $node->save();
    
    $this->messenger()->addStatus($this->t('Expense report saved successfully.'));
    $form_state->setRedirect('zivi_spesen.dashboard');
  }

}
