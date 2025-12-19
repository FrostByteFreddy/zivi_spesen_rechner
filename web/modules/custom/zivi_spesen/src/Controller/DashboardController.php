<?php

namespace Drupal\zivi_spesen\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for Zivi Spesen routes.
 */
class DashboardController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function content() {
    if ($this->currentUser()->isAnonymous()) {
      return new RedirectResponse(Url::fromRoute('user.login')->toString());
    }

    $roles = $this->currentUser()->getRoles();
    $is_admin = in_array('administrator', $roles);
    
    // Fetch nodes based on role.
    $query = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'spesenabrechnung')
      ->sort('created', 'DESC');

    if (!$is_admin) {
      $query->condition('uid', $this->currentUser()->id());
    }

    $nids = $query->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

    $items = [];
    foreach ($nodes as $node) {
      $status = $node->get('field_status')->value;
      $total = $node->get('field_total_sum')->value;
      
      $actions = [
        'view' => [
          'title' => 'View',
          'url' => $node->toUrl()->toString(),
        ],
      ];

      if ($status === 'Approved') {
        // Link to Entity Print PDF export.
        $actions['pdf'] = [
          'title' => 'Download PDF',
          'url' => Url::fromRoute('entity_print.view.export', [
            'export_type' => 'pdf',
            'entity_type' => 'node',
            'entity_id' => $node->id(),
          ])->toString(),
        ];
      } else {
        // Allow editing if not approved (or maybe always? Let's restrict Approved for safety)
        // Actually, let's allow editing for Draft and Submitted.
        $actions['edit'] = [
          'title' => 'Edit',
          'url' => Url::fromRoute('zivi_spesen.edit_report', ['node' => $node->id()])->toString(),
        ];
      }

      $items[] = [
        'id' => $node->id(),
        'title' => $node->getTitle(),
        'status' => $status,
        'date_range' => $node->get('field_date_range')->value . ' - ' . $node->get('field_date_range')->end_value,
        'total' => number_format((float)$total, 2, '.', "'"),
        'url' => $node->toUrl()->toString(),
        'actions' => $actions,
      ];
    }

    if ($is_admin) {
      return [
        '#theme' => 'dashboard_admin',
        '#items' => $items,
        '#attached' => [
          'library' => [
            'zivi_spesen/dashboard',
          ],
        ],
      ];
    }
    else {
      return [
        '#theme' => 'dashboard_zivi',
        '#items' => $items,
        '#attached' => [
          'library' => [
            'zivi_spesen/dashboard',
          ],
        ],
      ];
    }
  }

}
