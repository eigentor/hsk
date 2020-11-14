<?php

namespace Drupal\hsk_images\Field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;

/**
 * Formatter to display custom default images in News Article teasers on frontpage.
 *
 * @FieldFormatter (
 *   id = "news_front_image",
 *   label = @Translation("Custom default images"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */

class NewsFrontImageFormatter extends ImageFormatterBase {

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    return $elements;
  }

}
