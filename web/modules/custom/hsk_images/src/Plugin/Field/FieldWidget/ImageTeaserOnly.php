<?php

/**
 * @file
 * Contains
 *     \Drupal\nice_imagefield_widget\Plugin\Field\FieldWidget\NiceImageWidget.
 */

namespace Drupal\hsk_images\Plugin\Field\FieldWidget;

use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'nice_image_widget' widget.
 *
 * @FieldWidget(
 *   id = "hsk_teaser_only",
 *   label = @Translation("Teaser only image"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class NiceImageWidget extends ImageWidget {

}
