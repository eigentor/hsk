<?php
/**
 * @file
 * Contains \Drupal\hello_world\Controller\HelloController.
 */

namespace Drupal\glue\Controller;

use Drupal\Core\Controller\Controllerbase;

class HelloController extends ControllerBase {
  public function content(){
    return array(
      '#type' => 'markup',
      '#markup' => $this->t('Hello World'),
    );
  }
}