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

//    $raw_pgn_viewer = '<link type="text/css" rel="stylesheet" href="https://pgn.chessbase.com/CBReplay.css" />';
//    $raw_pgn_viewer .= '<script type="text/javascript" src="https://pgn.chessbase.com/jquery-3.0.0.min.js"></script>';
//    $raw_pgn_viewer .= '<script type="text/javascript" src="https://pgn.chessbase.com/cbreplay.js"></script>';
//    $elements[0] = [
//      '#type' => 'processed_text',
//      '#text' => $raw_pgn_viewer,
//      '#format' => 'full_html',
//    ];
    foreach($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => 'cbreplay'
        ],
        '#value' => $item->value,
      ];
    }
    return $elements;
  }
}