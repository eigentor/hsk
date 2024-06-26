<?php

/**
 * @file
 * Provide views data for view_unpublished.module.
 */

declare(strict_types=1);

use Drupal\view_unpublished\Plugin\views\filter\NodeStatus;

/**
 * Implements hook_views_plugins_filter_alter().
 */
function view_unpublished_views_plugins_filter_alter(array &$plugins): void {
  // Change the 'status' handler class.
  $plugins['node_status']['class'] = NodeStatus::class;
}
