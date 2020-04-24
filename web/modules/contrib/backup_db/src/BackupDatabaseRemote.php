<?php

/**
 * @file
 * Contains \Drupal\backup_db\BackupDatabaseRemote.
 */

namespace Drupal\backup_db;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * BackupDatabaseRemote extends BackupDatabase
 */
class BackupDatabaseRemote extends BackupDatabase {

  /**
   * Complete remote export.
   */
  function complete() {
    // Download the export file.
    $this->download();

    // Remove the export file from the server.
    file_unmanaged_delete($this->file_location);
  }

  /**
   * Download file (temp)
   *
   * @todo, implement BinaryFileResponse without trustXSendfileTypeHeader
   * we can't expect everyone to have access to their webserver config.
   */
  function download() {
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $this->content_type);
    header('Content-Disposition: inline; filename=' . basename($this->file_location));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($this->file_location));
    @ob_clean();
    flush();

    readfile($this->file_location);
  }
}
