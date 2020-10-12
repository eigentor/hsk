<?php

namespace \Drupal\hsk_migrate\Plugin\Block;

/**
 * Test-Block
 *
 * @Block (
 *   id = "hsk_test_block",
 *   admin_label = @Translation ("A Test Block")
 *   catagory = HSK
 *   )
 */

class HskTestBlock extends BlockBase {

  public function build() {
    return[
      '#markup' => 'Dies ist nur ein Test'
    ];
  }
}
