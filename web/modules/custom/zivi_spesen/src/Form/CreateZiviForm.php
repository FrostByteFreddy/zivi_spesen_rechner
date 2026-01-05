<?php

namespace Drupal\zivi_spesen\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form for editors to create a new Zivi user.
 */
class CreateZiviForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'zivi_spesen_create_zivi_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (!$this->currentUser()->hasPermission('edit any spesenabrechnung content')) {
      $this->messenger()->addError($this->t('Du hast keine Berechtigung, Zivis zu erstellen.'));
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
        '#markup' => '<h1 class="text-2xl font-bold text-gray-900">' . $this->t('Neuen Zivi erstellen') . '</h1>',
      ],
      'description' => [
        '#markup' => '<p class="mt-2 text-sm text-gray-600">' . $this->t('Erfasse hier die Stammdaten für einen neuen Zivildienstleistenden.') . '</p>',
      ],
    ];

    // Form body
    $form['wrapper']['card']['body'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['px-8', 'py-8', 'space-y-8']],
    ];

    // Grid for Username and Email
    $form['wrapper']['card']['body']['account_grid'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-6']],
    ];

    $form['wrapper']['card']['body']['account_grid']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Benutzername'),
      '#required' => TRUE,
      '#description' => $this->t('Der Login-Name für den Zivi.'),
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
        'placeholder' => 'z.B. max.mustermann',
      ],
    ];

    $form['wrapper']['card']['body']['account_grid']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('E-Mail-Adresse'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
        'placeholder' => 'max@beispiel.ch',
      ],
    ];

    // Password field
    $form['wrapper']['card']['body']['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Passwort'),
      '#required' => TRUE,
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
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
      ],
    ];

    $form['wrapper']['card']['body']['service_grid']['field_service_end'] = [
      '#type' => 'date',
      '#title' => $this->t('Letzter Einsatztag'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
      ],
    ];

    // Full Name
    $form['wrapper']['card']['body']['field_full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Vollständiger Name'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
        'placeholder' => 'Max Mustermann',
      ],
    ];

    // Address
    $form['wrapper']['card']['body']['field_address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Adresse'),
      '#required' => TRUE,
      '#rows' => 3,
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm'],
        'placeholder' => "Strasse 123\n8000 Zürich",
      ],
    ];

    // IBAN
    $form['wrapper']['card']['body']['field_iban'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IBAN'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['block', 'w-full', 'rounded-xl', 'border-gray-300', 'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'sm:text-sm', 'font-mono'],
        'placeholder' => 'CH00 0000 0000 0000 0000 0',
      ],
    ];

    // Actions
    $form['wrapper']['card']['footer'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['px-8', 'py-6', 'bg-gray-50', 'border-t', 'border-gray-100', 'flex', 'justify-end']],
    ];

    $form['wrapper']['card']['footer']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Zivi erstellen'),
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
    $username = $form_state->getValue('username');
    $email = $form_state->getValue('email');
    $password = $form_state->getValue('password');
    $start_date = $form_state->getValue('field_service_start');
    $end_date = $form_state->getValue('field_service_end');

    // Check if username exists
    if (user_load_by_name($username)) {
      $form_state->setErrorByName('username', $this->t('Dieser Benutzername ist bereits vergeben.'));
    }

    // Check if email exists
    if (user_load_by_mail($email)) {
      $form_state->setErrorByName('email', $this->t('Diese E-Mail-Adresse wird bereits verwendet.'));
    }

    // Check password length
    if (strlen($password) < 6) {
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

    // Create user entity
    $user = User::create([
      'name' => $values['username'],
      'mail' => $values['email'],
      'status' => 1,
      'roles' => ['zivi'],
      'field_full_name' => $values['field_full_name'],
      'field_address' => $values['field_address'],
      'field_iban' => $values['field_iban'],
      'field_service_start' => $values['field_service_start'],
      'field_service_end' => $values['field_service_end'],
    ]);

    // Set the provided password
    $user->setPassword($values['password']);

    $user->save();

    $this->messenger()->addStatus($this->t('Zivi %name wurde erfolgreich erstellt.', ['%name' => $values['field_full_name']]));
    $form_state->setRedirect('zivi_spesen.dashboard');
  }

}
