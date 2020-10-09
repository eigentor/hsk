<?php

namespace Drupal\hsk_migrate\Plugin\migrate\source;

use Drupal\migrate_plus\Plugin\source\Url;
use Drupal\migrate\Row;
use Drupal\Core\Database\Database;

/**
 * Source plugin to only update existing player nodes
 * and match them to the Schachbund API via Member No.
 *
 * @MigrateSource (
 *   id = "hsk_player_dwz",
 *   title = @Translation ("Rearranage Source data for Player DWZ")
 */
class PlayerDwz extends Url {

  /**
   * {@inheritdoc}
   */
  function prepareRow(Row $row) {
    return parent::prepareRow($row);
  }

}

