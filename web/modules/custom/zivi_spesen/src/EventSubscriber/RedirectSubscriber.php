<?php

namespace Drupal\zivi_spesen\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Redirects the front page to the dashboard.
 */
class RedirectSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkForRedirection'];
    return $events;
  }

  /**
   * Checks if the current request is for the front page and redirects.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function checkForRedirection(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    // Check if we are at the root path.
    if ($path === '/') {
      $url = Url::fromRoute('zivi_spesen.dashboard')->toString();
      $response = new RedirectResponse($url);
      $event->setResponse($response);
    }
  }

}
