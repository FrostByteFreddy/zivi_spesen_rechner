<?php

namespace Drupal\zivi_spesen\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form for editors to edit an existing Zivi user.
 */
class EditZiviForm extends FormBase {

  /**
   * The user being edited.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'zivi_spesen_edit_zivi_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $user = NULL) {
    if (!$this->currentUser()->hasPermission('edit any spesenabrechnung content')) {
      $this->messenger()->addError($this->t('Du hast keine Berechtigung, Zivis zu bearbeiten.'));
      return new RedirectResponse(Url::fromRoute('zivi_spesen.dashboard')->toString());
    }

    $this->user = User::load($user);
    if (!$this->user || !in_array('zivi', $this->user->getRoles())) {
      $this->messenger()->addError($this->t('Zivi nicht gefunden.'));
      return new RedirectResponse(Url::fromRoute('zivi_spesen.dashboard')->toString());
    }

    $form['#attached']['library'][] = 'zivi_spesen/app_styling';

    // Outer wrapper for centering and background
    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['max-w-3xl', 'mx-auto', 'px-4', 'sm:px-6', 'lg:px-8', 'py-12']],
    ];

    // Back link
    $form['wrapper']['back_link'] = [
      '#type' => 'link',
      '#title' => $this->t('Zurück zum Dashboard'),
      '#url' => Url::fromRoute('zivi_spesen.dashboard'),
      '#attributes' => [
        'class' => ['text-indigo-600', 'hover:text-indigo-900', 'text-sm', 'flex', 'items-center', 'mb-6', 'transition-colors'],
      ],
      '#prefix' => '<div class="flex items-center">',
      '#suffix' => '</div>',
    ];

    // Card container
    $form['wrapper']['card'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['bg-white', 'rounded-2xl', 'shadow-xl', 'border', 'border-gray-100', 'overflow-hidden']],
    ];

    // Header section
    $form['wrapper']['card']['header'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['px-8', 'py-8', 'bg-gray-50', 'border-b', 'border-gray-100']],
      'title' => [
        '#markup' => '<h1 class="text-2xl font-bold text-gray-900">' . $this->t('Zivi bearbeiten: @name', ['@name' => $this->user->getDisplayName()]) . '</h1>',
      ],
    ];

    // Form body
    $form['wrapper']['card']['body'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['px-8', 'py-8', 'space-y-8']],
    ];

    // Account details (Read-only or editable?)
    // Let's allow editing email but maybe not username to keep it simple.
    $form['wrapper']['card']['body']['account_grid'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-6']],
    ];

    $form['wrapper']['card']['body']['account_grid']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Benutzername'),
      '#default_value' => $this->user->getAccountName(),
      '#disabled' => TRUE,
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'bg-gray-50', 'border-gray-300', 'text-gray-500', 'sm:text-sm'],
      ],
    ];

    $form['wrapper']['card']['body']['account_grid']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('E-Mail-Adresse'),
      '#required' => TRUE,
      '#default_value' => $this->user->getEmail(),
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
      ],
    ];

    // Password field (Optional)
    $form['wrapper']['card']['body']['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Neues Passwort (leer lassen für keine Änderung)'),
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
        'placeholder' => '••••••••',
      ],
    ];

    // Grid for Service Dates
    $form['wrapper']['card']['body']['service_grid'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-6']],
    ];

    $form['wrapper']['card']['body']['service_grid']['field_service_start'] = [
      '#type' => 'date',
      '#title' => $this->t('Erster Einsatztag'),
      '#required' => TRUE,
      '#default_value' => $this->user->get('field_service_start')->value,
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
      ],
    ];

    $form['wrapper']['card']['body']['service_grid']['field_service_end'] = [
      '#type' => 'date',
      '#title' => $this->t('Letzter Einsatztag'),
      '#required' => TRUE,
      '#default_value' => $this->user->get('field_service_end')->value,
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
      ],
    ];

    // Full Name
    $form['wrapper']['card']['body']['field_full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Vollständiger Name'),
      '#required' => TRUE,
      '#default_value' => $this->user->get('field_full_name')->value,
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
      ],
    ];

    // Address
    $form['wrapper']['card']['body']['field_address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Adresse'),
      '#required' => TRUE,
      '#rows' => 3,
      '#default_value' => $this->user->get('field_address')->value,
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
      ],
    ];

    // IBAN
    $form['wrapper']['card']['body']['field_iban'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IBAN'),
      '#required' => TRUE,
      '#default_value' => $this->user->get('field_iban')->value,
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm', 'font-mono'],
      ],
    ];

    // Actions
    $form['wrapper']['card']['footer'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['px-8', 'py-6', 'bg-gray-50', 'border-t', 'border-gray-100', 'flex', 'justify-end']],
    ];

    $form['wrapper']['card']['footer']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Änderungen speichern'),
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => ['inline-flex', 'justify-center', 'items-center', 'px-6', 'py-3', 'border', 'border-transparent', 'text-base', 'font-medium', 'rounded-xl', 'shadow-sm', 'text-white', 'bg-indigo-600', 'hover:bg-indigo-700', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-indigo-500', 'transition-all', 'duration-200'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $password = $form_state->getValue('password');
    $start_date = $form_state->getValue('field_service_start');
    $end_date = $form_state->getValue('field_service_end');

    // Check if email exists (excluding current user)
    $existing_user = user_load_by_mail($email);
    if ($existing_user && $existing_user->id() != $this->user->id()) {
      $form_state->setErrorByName('email', $this->t('Diese E-Mail-Adresse wird bereits verwendet.'));
    }

    // Check password length if provided
    if (!empty($password) && strlen($password) < 6) {
      $form_state->setErrorByName('password', $this->t('Das Passwort muss mindestens 6 Zeichen lang sein.'));
    }

    // Check dates
    if ($start_date && $end_date && $start_date > $end_date) {
      $form_state->setErrorByName('field_service_end', $this->t('Das Enddatum muss nach dem Startdatum liegen.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->user->setEmail($values['email']);
    $this->user->set('field_full_name', $values['field_full_name']);
    $this->user->set('field_address', $values['field_address']);
    $this->user->set('field_iban', $values['field_iban']);
    $this->user->set('field_service_start', $values['field_service_start']);
    $this->user->set('field_service_end', $values['field_service_end']);

    if (!empty($values['password'])) {
      $this->user->setPassword($values['password']);
    }

    $this->user->save();

    $this->messenger()->addStatus($this->t('Zivi %name wurde erfolgreich aktualisiert.', ['%name' => $values['field_full_name']]));
    $form_state->setRedirect('zivi_spesen.dashboard');
  }

}
