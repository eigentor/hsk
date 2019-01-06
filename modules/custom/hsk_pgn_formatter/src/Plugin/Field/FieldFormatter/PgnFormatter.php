<?php

namespace Drupal\hsk_pgn_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Formatter to display PGN Code inside a textfield with the Chessbase PGN Viewer.
 *
 * @FieldFormatter (
 *   id = "hsk_pgn_display",
 *   label = @Translation("Display PGN Code with the Chessbase PGN Viewer"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */

class PgnFormatter extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    // TODO: Implement viewElements() method.
    return $elements;
  }
}