<?php

namespace Drupal\zivi_spesen\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
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
    // Check if report is approved - Zivis cannot edit approved reports
    $is_editor = $this->currentUser()->hasPermission('edit any spesenabrechnung content');
    $status = ($node && !$node->get('field_status')->isEmpty()) ? $node->get('field_status')->value : 'draft';

    if ($node && $status === 'approved' && !$is_editor) {
      $this->messenger()->addWarning($this->t('Diese Spesenabrechnung wurde bereits genehmigt und kann nicht mehr bearbeitet werden.'));
      return [
        'message' => [
          '#markup' => '<div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-sm font-sans text-center">
            <h2 class="text-xl font-bold text-gray-900 mb-4">' . $this->t('Abrechnung genehmigt') . '</h2>
            <p class="text-gray-600 mb-6">' . $this->t('Diese Spesenabrechnung wurde bereits genehmigt und kann nicht mehr bearbeitet werden.') . '</p>
            <a href="/dashboard" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">' . $this->t('Zurück zum Dashboard') . '</a>
          </div>',
        ],
      ];
    }

    $form['#theme'] = 'expense_report_form';
    $form['#attached']['library'][] = 'zivi_spesen/calculator';
    $form['#attached']['library'][] = 'zivi_spesen/app_styling';
    $form['#cache']['max-age'] = 0;
    
    // Store node in form state for submit handler
    if ($node) {
      $form_state->set('node', $node);
    }

    // Load Zivi user to get service dates
    $zivi = $node ? $node->getOwner() : User::load($this->currentUser()->id());
    $service_start_val = $zivi->get('field_service_start')->value;
    $service_end_val = $zivi->get('field_service_end')->value;

    $form['#attached']['drupalSettings']['zivi_spesen']['service_start'] = $service_start_val;
    $form['#attached']['drupalSettings']['zivi_spesen']['service_end'] = $service_end_val;

    // 1. Header / Date Range
    $form['header'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['expense-form-header']],
    ];

    $now = new DrupalDateTime();
    $month_start = new DrupalDateTime('first day of this month');
    $month_end = new DrupalDateTime('last day of this month');

    // Default dates for new reports
    $start_date = $month_start;
    $end_date = $month_end;

    if (!$node) {
      // For new reports, if service start is in this month, start there
      if ($service_start_val) {
        $s_start = DrupalDateTime::createFromFormat('Y-m-d', $service_start_val);
        if ($s_start->format('Y-m') === $now->format('Y-m') && $s_start > $month_start) {
          $start_date = $s_start;
        }
      }
      // If service end is in this month, end there
      if ($service_end_val) {
        $s_end = DrupalDateTime::createFromFormat('Y-m-d', $service_end_val);
        if ($s_end->format('Y-m') === $now->format('Y-m') && $s_end < $month_end) {
          $end_date = $s_end;
        }
      }
    }
    
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
      '#title' => $this->t('Startdatum'),
      '#default_value' => $start_date->format('Y-m-d'),
      '#attributes' => ['class' => ['expense-date-start']],
    ];

    $form['header']['date_range_end'] = [
      '#type' => 'date',
      '#title' => $this->t('Enddatum'),
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
        '#attributes' => ['class' => ['grid', 'grid-cols-1', 'md:grid-cols-12', 'gap-4', 'items-center', 'py-4', 'md:py-2', 'border-b', 'border-gray-100']],
      ];

      // Cell 1: Item (3 cols)
      $form['expenses'][$key]['item_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['md:col-span-3', 'font-bold', 'md:font-medium', 'text-gray-900', 'text-lg', 'md:text-sm']],
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
        '#attributes' => ['class' => ['md:col-span-2', 'text-gray-600', 'flex', 'md:block', 'justify-between', 'items-center']],
        'label' => ['#markup' => '<span class="md:hidden text-xs font-semibold uppercase text-gray-400">Rate:</span>'],
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
        '#attributes' => ['class' => ['md:col-span-2', 'flex', 'md:block', 'justify-between', 'items-center']],
        'label' => ['#markup' => '<span class="md:hidden text-xs font-semibold uppercase text-gray-400">Days:</span>'],
        'quantity' => [
          '#type' => 'number',
          '#title' => $this->t('Tage'),
          '#title_display' => 'invisible',
          '#default_value' => $default_qty,
          '#attributes' => ['class' => ['expense-quantity', 'w-full', 'rounded-md', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'], 'min' => 0],
        ],
      ];

      // Cell 4: Total (2 cols)
      $form['expenses'][$key]['total_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['md:col-span-2', 'flex', 'md:block', 'justify-between', 'items-center']],
        'label' => ['#markup' => '<span class="md:hidden text-xs font-semibold uppercase text-gray-400">Total:</span>'],
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
        '#attributes' => ['class' => ['md:col-span-2', 'hidden', 'md:block']],
      ];

      // Cell 6: Actions (1 col) - Empty
      $form['expenses'][$key]['actions_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['md:col-span-1', 'hidden', 'md:block']],
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
        '#attributes' => ['class' => ['grid', 'grid-cols-1', 'md:grid-cols-12', 'gap-4', 'items-center', 'py-6', 'md:py-3', 'border-b', 'border-gray-100', 'relative']],
      ];

      // Cell 1: Item Input (3 cols)
      $form['expenses'][$key]['item_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['md:col-span-3']],
        'item' => [
          '#type' => 'textfield',
          '#title' => $this->t('Beschreibung'),
          '#title_display' => 'invisible',
          '#default_value' => $default_desc,
          '#attributes' => ['class' => ['expense-type-input', 'w-full', 'rounded-md', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'], 'placeholder' => 'Beschreibung'],
        ],
      ];
      
      // Cell 2: Rate Input (2 cols)
      $form['expenses'][$key]['rate_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['md:col-span-2']],
        'rate_col' => [
          '#type' => 'number',
          '#step' => '0.05',
          '#title' => $this->t('Satz'),
          '#title_display' => 'invisible',
          '#default_value' => $default_rate,
          '#attributes' => ['class' => ['expense-rate-input', 'w-full', 'rounded-md', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'], 'placeholder' => 'Satz'],
        ],
      ];

      // Cell 3: Quantity (2 cols)
      $form['expenses'][$key]['quantity_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['md:col-span-2']],
        'quantity' => [
          '#type' => 'number',
          '#title' => $this->t('Anzahl'),
          '#title_display' => 'invisible',
          '#default_value' => $default_qty,
          '#attributes' => ['class' => ['expense-quantity', 'w-full', 'rounded-md', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'], 'placeholder' => 'Anzahl'],
        ],
      ];

      // Cell 4: Total (2 cols)
      $form['expenses'][$key]['total_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['md:col-span-2']],
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
        '#attributes' => ['class' => ['md:col-span-2']],
        'receipt' => [
          '#type' => 'managed_file',
          '#title' => $this->t('Beleg'),
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
        '#attributes' => ['class' => ['md:col-span-1', 'absolute', 'top-4', 'right-0', 'md:relative', 'md:top-0']],
        'remove' => [
          '#type' => 'submit',
          '#value' => 'Entfernen',
          '#name' => 'remove_row_' . ($i + 1),
          '#submit' => ['::removeRow'],
          '#ajax' => [
            'callback' => '::addmoreCallback',
            'wrapper' => 'expenses-table-wrapper',
          ],
          '#attributes' => ['class' => ['text-xs', 'text-red-600', 'bg-white', 'border', 'border-red-200', 'hover:bg-red-50', 'hover:border-red-300', 'shadow-sm', 'rounded-md', 'px-2', 'py-1', 'cursor-pointer', 'transition-all', 'duration-200']],
          '#limit_validation_errors' => [],
        ],
      ];
    }

    // Add Row Button
    $form['add_row'] = [
      '#type' => 'submit',
      '#value' => $this->t('Spesen hinzufügen'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'expenses-table-wrapper',
      ],
      '#attributes' => ['class' => ['inline-block', 'px-4', 'py-2', 'border', 'border-indigo-200', 'text-sm', 'font-medium', 'rounded-lg', 'text-indigo-600', 'bg-white', 'hover:bg-indigo-50', 'hover:border-indigo-300', 'shadow-sm', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-indigo-500', 'cursor-pointer', 'transition-all', 'duration-200']],
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

    // 4. Editor Comment
    if ($this->currentUser()->hasPermission('edit any spesenabrechnung content')) {
      $editor_comment = '';
      if ($node && $node->hasField('field_editor_comment')) {
        $editor_comment = $node->get('field_editor_comment')->value;
      }

      $form['editor_comment'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Editor Kommentar'),
        '#default_value' => $editor_comment,
        '#attributes' => ['class' => ['w-full', 'rounded-md', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm', 'mt-4']],
        '#description' => $this->t('Dieser Kommentar wird dem Zivi per E-Mail gesendet und im Dashboard angezeigt.'),
      ];
    }

    // 5. Actions
    $form['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-actions', 'flex', 'justify-end', 'gap-4']],
    ];

    // Only show "Save Draft" if the status is "draft"
    if ($status === 'draft') {
      $form['actions']['save_draft'] = [
        '#type' => 'submit',
        '#value' => $this->t('Speichern (Entwurf)'),
        '#name' => 'save_draft',
        '#attributes' => ['class' => ['w-full', 'block', 'text-center', 'py-2', 'px-4', 'border', 'border-gray-300', 'rounded-lg', 'shadow-sm', 'hover:shadow-md', 'text-sm', 'font-medium', 'text-gray-700', 'bg-white', 'hover:bg-gray-50', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-indigo-500', 'cursor-pointer', 'transition-all', 'duration-200', 'mb-3']],
      ];
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Einreichen'),
      '#name' => 'submit_report',
      '#button_type' => 'primary',
      '#attributes' => ['class' => ['w-full', 'block', 'text-center', 'py-2', 'px-4', 'border', 'border-transparent', 'rounded-lg', 'shadow-md', 'hover:shadow-lg', 'text-sm', 'font-semibold', 'tracking-wide', 'text-white', 'bg-indigo-600', 'hover:bg-indigo-700', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-indigo-500', 'cursor-pointer', 'transition-all', 'duration-200']],
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
      $date_timestamp = strtotime($values['date_range_start']);
      $month_german = $this->getGermanMonth(date('n', $date_timestamp));
      $node->setTitle('Spesenabrechnung ' . $month_german . ' ' . date('Y', $date_timestamp));
      $node->set('field_date_range', [
        'value' => $values['date_range_start'],
        'end_value' => $values['date_range_end'],
      ]);
      $node->set('field_total_sum', $values['total_sum']);
      
      // Update status if submitted or saved as draft
      $triggering_element = $form_state->getTriggeringElement();
      if (isset($triggering_element['#name'])) {
        if ($triggering_element['#name'] === 'submit_report') {
          $node->set('field_status', 'submitted');
        } elseif ($triggering_element['#name'] === 'save_draft') {
          $node->set('field_status', 'draft');
        }
      }
      
      // Save Editor Comment
      if (isset($values['editor_comment'])) {
        $node->set('field_editor_comment', $values['editor_comment']);
      }
      
      // Clear existing paragraphs to rebuild them
      // This is simpler than trying to match and update
      $node->set('field_expense_items', []);
    } else {
      // Determine status based on button clicked
      $triggering_element = $form_state->getTriggeringElement();
      $status = 'draft'; // Default
      if (isset($triggering_element['#name']) && $triggering_element['#name'] === 'submit_report') {
        $status = 'submitted';
      }

      // Create Node
      $date_timestamp = strtotime($values['date_range_start']);
      $month_german = $this->getGermanMonth(date('n', $date_timestamp));
      $node = Node::create([
        'type' => 'spesenabrechnung',
        'title' => 'Spesenabrechnung ' . $month_german . ' ' . date('Y', $date_timestamp),
        'uid' => $user->id(),
        'field_date_range' => [
          'value' => $values['date_range_start'],
          'end_value' => $values['date_range_end'],
        ],
        'field_total_sum' => $values['total_sum'],
        'field_status' => $status,
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
      // Update existing node
      $date_timestamp = strtotime($values['date_range_start']);
      $month_german = $this->getGermanMonth(date('n', $date_timestamp));
      $node->setTitle('Spesenabrechnung ' . $month_german . ' ' . date('Y', $date_timestamp));
      $node->set('field_date_range', [
        'value' => $values['date_range_start'],
        'end_value' => $values['date_range_end'],
      ]);
      $node->set('field_total_sum', $values['total_sum']);
      $node->set('field_expense_items', $items_to_save);
      $node->save();
    }
    
    $this->messenger()->addStatus($this->t('Spesenabrechnung erfolgreich gespeichert.'));
    $form_state->setRedirect('zivi_spesen.dashboard');
  }

  /**
   * Helper to get German month name.
   */
  private function getGermanMonth($month_number) {
    $months = [
      1 => 'Januar', 2 => 'Februar', 3 => 'März', 4 => 'April',
      5 => 'Mai', 6 => 'Juni', 7 => 'Juli', 8 => 'August',
      9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember'
    ];
    return $months[$month_number] ?? '';
  }

}
