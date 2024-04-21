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
    $entity_type_manager = \Drupal::entityTypeManager();

    // return an empty array if we find no matching tags
    $build = [];

    if ($node->bundle() == 'article') {
      if ($node->hasField('field_tags')) {
        // Get terms from field_tags
        if (!empty($node->field_tags->getValue())) {
          $term_names = [];
          $term_ids = array_column($node->field_tags->getValue(), 'target_id');
          foreach ($term_ids as $tid) {
            if (!empty($entity_type_manager->getStorage('taxonomy_term')
              ->load($tid))) {
              // Extract tags that match "1. Bundesliga" or "2. Bundesliga"
              if (in_array($tid, ['26', '24'])) {
                $myterm = $entity_type_manager->getStorage('taxonomy_term')
                  ->load($tid);
                $term_names[] = $myterm->get('name')->value;
              }
            }
            /*$term = $item->entity;
            $lars = 'gondorf';*/
          }
          // Output the terms as unordered list.
          $build = [
            '#theme' => 'item_list',
            '#list_type' => 'ul',
            '#items' => $term_names,
            '#attributes' => ['class' => 'tag-list'],
          ];
        }
      }
    }

    return $build;
  }

}
