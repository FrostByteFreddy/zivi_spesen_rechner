<?php

namespace Drupal\zivi_spesen\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Form for reviewing an expense report (Editor only).
 */
class ReviewReportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'zivi_spesen_review_report_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Node $node = NULL) {
    if (!$node) {
      return [];
    }

    $form_state->set('node', $node);

    // $form['#theme'] = 'expense_report_form'; // Removed to use default rendering
    // Actually, we'll render this inside the ReviewController template, so standard form structure is fine.

    $editor_comment = '';
    if ($node->hasField('field_editor_comment')) {
      $editor_comment = $node->get('field_editor_comment')->value;
    }

    $form['editor_comment'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Kommentar / Rückmeldung'),
      '#default_value' => $editor_comment,
      '#attributes' => ['class' => ['w-full', 'rounded-md', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm', 'h-32']],
      '#description' => $this->t('Dieser Kommentar wird dem Zivi per E-Mail gesendet.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['mt-4']],
    ];
    
    $form['actions']['request_changes'] = [
      '#type' => 'submit',
      '#value' => $this->t('Änderung anfordern'),
      '#name' => 'request_changes',
      '#attributes' => ['class' => ['w-full', 'block', 'text-center', 'py-2', 'px-4', 'border', 'border-red-300', 'rounded-lg', 'shadow-sm', 'hover:shadow-md', 'text-sm', 'font-medium', 'text-red-700', 'bg-white', 'hover:bg-red-50', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-red-500', 'cursor-pointer', 'transition-all', 'duration-200', 'mb-3']],
    ];

    $form['actions']['approve'] = [
      '#type' => 'submit',
      '#value' => $this->t('Genehmigen'),
      '#name' => 'approve_report',
      '#button_type' => 'primary',
      '#attributes' => ['class' => ['w-full', 'block', 'text-center', 'py-2', 'px-4', 'border', 'border-transparent', 'rounded-lg', 'shadow-md', 'hover:shadow-lg', 'text-sm', 'font-semibold', 'tracking-wide', 'text-white', 'bg-green-600', 'hover:bg-green-700', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-green-500', 'cursor-pointer', 'transition-all', 'duration-200']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $form_state->get('node');
    $values = $form_state->getValues();

    if ($node) {
      if (isset($values['editor_comment'])) {
        $node->set('field_editor_comment', $values['editor_comment']);
      }
      
      $triggering_element = $form_state->getTriggeringElement();
      if (isset($triggering_element['#name'])) {
        if ($triggering_element['#name'] === 'approve_report') {
          $node->set('field_status', 'approved');
          $this->messenger()->addStatus($this->t('Abrechnung genehmigt.'));
        } elseif ($triggering_element['#name'] === 'request_changes') {
          $node->set('field_status', 'change_requested');
          $this->messenger()->addStatus($this->t('Änderungen angefordert.'));
        }
      }
      
      $node->save();
    }
    
    $form_state->setRedirect('zivi_spesen.dashboard');
  }

}
