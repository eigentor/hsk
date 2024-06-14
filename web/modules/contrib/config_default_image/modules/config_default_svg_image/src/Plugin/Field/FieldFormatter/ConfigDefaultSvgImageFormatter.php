<?php

namespace Drupal\config_default_svg_image\Plugin\Field\FieldFormatter;

use Drupal\config_default_image\Plugin\Field\FieldFormatter\ConfigDefaultImageFormatterTrait;
use Drupal\svg_image\Plugin\Field\FieldFormatter\SvgImageFormatter;

/**
 * Plugin implementation of the 'image' formatter.
 *
 * @FieldFormatter(
 *   id = "config_default_svg_image",
 *   label = @Translation("Image or default image (SVG compatible)"),
 *   field_types = {
 *     "image"
 *   },
 *   quickedit = {
 *     "editor" = "image"
 *   }
 * )
 */
class ConfigDefaultSvgImageFormatter extends SvgImageFormatter {

  use ConfigDefaultImageFormatterTrait;

}
