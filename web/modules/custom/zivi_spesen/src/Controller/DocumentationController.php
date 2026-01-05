<?php

namespace Drupal\zivi_spesen\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the documentation page.
 */
class DocumentationController extends ControllerBase {

  /**
   * Renders the documentation page.
   */
  public function view() {
    $user = $this->currentUser();
    $is_editor = $user->hasPermission('edit any spesenabrechnung content');
    $is_zivi = in_array('zivi', $user->getRoles());

    return [
      '#theme' => 'documentation',
      '#is_editor' => $is_editor,
      '#is_zivi' => $is_zivi,
      '#flow_diagram' => '/' . \Drupal::service('extension.list.module')->getPath('zivi_spesen') . '/images/flow.png',
    ];
  }

}
