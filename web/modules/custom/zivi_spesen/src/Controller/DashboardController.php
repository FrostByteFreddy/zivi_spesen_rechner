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
    $is_editor = in_array('editor', $roles);
    
    // Fetch nodes based on role.
    $query = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'spesenabrechnung')
      ->sort('created', 'DESC');

    if (!$is_admin && !$is_editor) {
      $query->condition('uid', $this->currentUser()->id());
    }

    $nids = $query->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

    $items = [];
    foreach ($nodes as $node) {
      $status = $node->get('field_status')->value;
      // Debug logging
      \Drupal::logger('zivi_spesen')->notice('Node @nid status raw: "@status"', ['@nid' => $node->id(), '@status' => $status]);
      
      if (empty($status) || $status === NULL || $status === '') {
        $status = 'Draft';
      }
      $status = trim($status);
      $total = $node->get('field_total_sum')->value;
      
      $actions = [
        'view' => [
          'title' => 'Ansehen',
          'url' => $node->toUrl()->toString(),
        ],
      ];

      // Link to Entity Print PDF export.
      $actions['pdf'] = [
        'title' => 'PDF herunterladen',
        'url' => Url::fromRoute('entity_print.view', [
          'export_type' => 'pdf',
          'entity_type' => 'node',
          'entity_id' => $node->id(),
        ])->toString(),
      ];

      if ($status !== 'approved' || $is_admin || $is_editor) {
        // Allow editing if not approved or if admin/editor
        if ($is_editor) {
          $actions['edit'] = [
            'title' => 'Prüfen',
            'url' => Url::fromRoute('zivi_spesen.review_report', ['node' => $node->id()])->toString(),
          ];
        } else {
          $actions['edit'] = [
            'title' => 'Bearbeiten',
            'url' => Url::fromRoute('zivi_spesen.edit_report', ['node' => $node->id()])->toString(),
          ];
        }
        
        // Only Zivi and Admin can delete. Editor cannot delete.
        if (!$is_editor) {
          $actions['delete'] = [
            'title' => 'Löschen',
            'url' => Url::fromRoute('entity.node.delete_form', ['node' => $node->id()])->toString(),
          ];
        }
      }

      // For Zivis, if approved, update the view action to point to the read-only preview
      if ($status === 'approved' && !$is_editor && !$is_admin) {
        $actions['view'] = [
          'title' => 'Ansehen',
          'url' => Url::fromRoute('zivi_spesen.view_report', ['node' => $node->id()])->toString(),
        ];
        // Remove edit action just in case (though it shouldn't be there)
        unset($actions['edit']);
      }

      $report_data = [
        'id' => $node->id(),
        'title' => $node->getTitle(),
        'status' => $status,
        'date_range' => $node->get('field_date_range')->value . ' - ' . $node->get('field_date_range')->end_value,
        'total' => number_format((float)$total, 2, '.', "'"),
        'url' => $node->toUrl()->toString(),
        'actions' => $actions,
        'zivi_uid' => $node->getOwnerId(),
        'zivi_name' => $node->getOwner()->getDisplayName(),
        'editor_comment' => $node->hasField('field_editor_comment') ? $node->get('field_editor_comment')->value : '',
      ];
      
      $items[] = $report_data;
    }

    if ($is_editor) {
      // Group by Zivi for Editor Dashboard
      $grouped_items = [];
      
      // Fetch all users with 'zivi' role to ensure they show up even without reports
      $zivi_query = \Drupal::entityQuery('user')
        ->accessCheck(TRUE)
        ->condition('roles', 'zivi')
        ->condition('status', 1);
      $zivi_uids = $zivi_query->execute();
      $zivi_users = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple($zivi_uids);
      
      foreach ($zivi_users as $zivi_user) {
        $full_name = $zivi_user->get('field_full_name')->value;
        $grouped_items[$zivi_user->id()] = [
          'name' => !empty($full_name) ? $full_name : $zivi_user->getDisplayName(),
          'service_start' => $zivi_user->get('field_service_start')->value,
          'service_end' => $zivi_user->get('field_service_end')->value,
          'edit_url' => Url::fromRoute('zivi_spesen.edit_zivi', ['user' => $zivi_user->id()])->toString(),
          'reports' => [],
        ];
      }

      foreach ($items as $item) {
        $uid = $item['zivi_uid'];
        if (isset($grouped_items[$uid])) {
          $grouped_items[$uid]['reports'][] = $item;
        }
      }

      return [
        '#theme' => 'dashboard_editor',
        '#items' => $grouped_items,
        '#create_zivi_url' => Url::fromRoute('zivi_spesen.create_zivi')->toString(),
        '#attached' => [
          'library' => [
            'zivi_spesen/dashboard',
          ],
        ],
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
    elseif ($is_admin) {
      return [
        '#theme' => 'dashboard_admin',
        '#items' => $items,
        '#attached' => [
          'library' => [
            'zivi_spesen/dashboard',
          ],
        ],
        '#cache' => [
          'max-age' => 0,
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
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
  }

}
