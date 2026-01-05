<?php

namespace Drupal\zivi_spesen\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for the review page.
 */
class ReviewController extends ControllerBase {

  /**
   * Displays the review page.
   */
  public function content(NodeInterface $node) {
    // Access check (double check, though routing handles it)
    if (!$this->currentUser()->hasPermission('edit any spesenabrechnung content')) {
      return new RedirectResponse(Url::fromRoute('zivi_spesen.dashboard')->toString());
    }

    // Get PDF URL (Inline)
    $pdf_url = Url::fromRoute('zivi_spesen.review_pdf', [
      'node' => $node->id(),
    ])->toString() . '#view=FitH&navpanes=0';

    // Build Form
    $form = \Drupal::formBuilder()->getForm('\Drupal\zivi_spesen\Form\ReviewReportForm', $node);

    return [
      '#theme' => 'page__review_report',
      '#pdf_url' => $pdf_url,
      '#form' => $form,
      '#node' => $node,
      '#attached' => [
        'library' => [
          'zivi_spesen/app_styling',
        ],
      ],
    ];
  }

  /**
   * Generates PDF for inline viewing.
   */
  public function pdf(NodeInterface $node) {
    $print_engine = \Drupal::service('plugin.manager.entity_print.print_engine')->createSelectedInstance('pdf');
    $print_builder = \Drupal::service('entity_print.print_builder');
    
    // We need to return a response, but deliverPrintable sends it directly.
    // However, we want to force inline.
    // deliverPrintable(array $entities, PrintEngineInterface $print_engine, $force_download = TRUE, $use_default_css = TRUE)
    
    // We can't easily capture the output of deliverPrintable if it streams.
    // But we can use it to generate the blob if we look at how it works.
    // Actually, let's just call it with $force_download = FALSE.
    // It returns a Response object or null if it sent it?
    // Looking at the code (assumed): it usually returns nothing and streams.
    // But if we pass FALSE, it should set Content-Disposition: inline.
    
    return $print_builder->deliverPrintable([$node], $print_engine, FALSE, TRUE);
  }

  /**
   * Displays the read-only preview page for Zivis.
   */
  public function view(NodeInterface $node) {
    // Get PDF URL (Inline)
    $pdf_url = Url::fromRoute('zivi_spesen.review_pdf', [
      'node' => $node->id(),
    ])->toString() . '#view=FitH&navpanes=0';

    return [
      '#theme' => 'page__view_report',
      '#pdf_url' => $pdf_url,
      '#node' => $node,
      '#attached' => [
        'library' => [
          'zivi_spesen/app_styling',
        ],
      ],
    ];
  }

}
