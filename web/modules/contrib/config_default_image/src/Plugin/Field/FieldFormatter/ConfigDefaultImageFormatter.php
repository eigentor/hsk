<?php

namespace Drupal\config_default_image\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;

/**
 * Plugin implementation of the 'image' formatter.
 *
 * @FieldFormatter(
 *   id = "config_default_image",
 *   label = @Translation("Image or default image"),
 *   field_types = {
 *     "image"
 *   },
 *   quickedit = {
 *     "editor" = "image"
 *   }
 * )
 */
class ConfigDefaultImageFormatter extends ImageFormatter {

  use ConfigDefaultImageFormatterTrait;

}
