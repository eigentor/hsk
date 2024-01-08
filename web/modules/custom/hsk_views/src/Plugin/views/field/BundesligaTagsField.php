<?php

namespace Drupal\hsk_views\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * a handler to show Bundesliga-related Tags in a View.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("bundesliga_tags")
 */
class BundesligaTagsField extends FieldPluginBase {
   /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    return $options;
  }

  public function render(ResultRow $values) {
    $node = $this->getEntity($values);

    $build = [];

    if ($node->bundle() == 'article') {
      if(!empty($node->field_tags->getValue())) {
        foreach($node->field_tags as $item) {
          $term = $item->entity;
          if(in_array($term->get('tid')->value, ['26','24'])) {
            $myterm = $term;
            $term_name = $myterm->get('name')->value;
            $build = ['#markup' => $term_name];
          }
        }
      }
    }

    return $build;
  }

}
