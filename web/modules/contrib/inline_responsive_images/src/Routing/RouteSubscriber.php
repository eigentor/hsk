<?php

namespace Drupal\inline_responsive_images\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\inline_responsive_images\Form\ResponsiveEditorImageDialog;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('editor.image_dialog')) {
      $route->addDefaults(['_form' => ResponsiveEditorImageDialog::class]);
    }
  }

}
