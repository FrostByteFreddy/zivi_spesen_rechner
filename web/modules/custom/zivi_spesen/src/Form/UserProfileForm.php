<?php

namespace Drupal\zivi_spesen\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for editing user profile details on a dedicated page.
 */
class UserProfileForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'zivi_user_profile_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $user_entity = \Drupal\user\Entity\User::load($user->id());

    $form['#attributes']['class'][] = 'max-w-2xl mx-auto';

    $form['header'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4']],
    ];

    $form['header']['title'] = [
      '#markup' => '<h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Mein Profil</h1>',
    ];

    $form['header']['back'] = [
      '#type' => 'link',
      '#title' => $this->t('Zurück zum Dashboard'),
      '#url' => Url::fromRoute('zivi_spesen.dashboard'),
      '#attributes' => [
        'class' => ['text-sm font-medium text-blue-600 hover:text-blue-500'],
      ],
    ];

    $form['profile_card'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['bg-white rounded-2xl shadow-xl border border-gray-100 p-6 md:p-8']],
    ];

    $form['profile_card']['field_full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Vollständiger Name'),
      '#default_value' => $user_entity->get('field_full_name')->value,
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all mb-6'],
        'placeholder' => 'Vorname Nachname',
      ],
    ];

    $form['profile_card']['grid'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['grid grid-cols-1 md:grid-cols-2 gap-6 mb-6']],
    ];

    $form['profile_card']['grid']['field_iban'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IBAN'),
      '#default_value' => $user_entity->get('field_iban')->value,
      '#attributes' => [
        'class' => ['w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all'],
        'placeholder' => 'CH00 0000 0000 0000 0000 0',
      ],
    ];

    $form['profile_card']['field_address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Adresse'),
      '#default_value' => $user_entity->get('field_address')->value,
      '#rows' => 3,
      '#attributes' => [
        'class' => ['w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all'],
        'placeholder' => "Strasse Nr.\nPLZ Ort",
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['mt-8 flex justify-end']],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Profil speichern'),
      '#attributes' => [
        'class' => ['w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl transition-all transform hover:scale-105 shadow-lg'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $user_entity = \Drupal\user\Entity\User::load($user->id());

    $user_entity->set('field_full_name', $form_state->getValue('field_full_name'));
    $user_entity->set('field_iban', $form_state->getValue('field_iban'));
    $user_entity->set('field_address', $form_state->getValue('field_address'));
    $user_entity->save();

    $this->messenger()->addStatus($this->t('Dein Profil wurde erfolgreich aktualisiert.'));
    $form_state->setRedirect('zivi_spesen.dashboard');
  }

}
