<?php

/**
 * @file
 * Contains \Drupal\backup_db\BackupDatabaseLocal.
 */

namespace Drupal\backup_db;

/**
 * BackupDatabaseLocal extends BackupDatabase
 */
class BackupDatabaseLocal extends BackupDatabase {

  /**
   * Complete local export.
   */
  function complete() {
    $user = \Drupal::currentUser();

    // Create a file entity.
    $file = entity_create('file', array(
      'uri' => $this->file_uri,
      'uid' => $user->id(),
      'status' => FILE_STATUS_PERMANENT,
    ));
    $file->save();

    // Insert history entry.
    if ($file->id()) {
      backup_db_history_insert(array(
        'fid' => $file->id(),
        'name' => $this->filename,
        'uri' => $this->file_uri
      ));
    }
    else {
      \Drupal::logger('backup_db')->error('File entity could not be created.');
    }
  }
}
