<?php

/**
 * @file
 * Contains \Drupal\backup_db\BackupDatabase.
 *
 * @todo, include and exclude table options...
 */

namespace Drupal\backup_db;

use Ifsnop\Mysqldump as IMysqldump;
use Drupal\Core\Database\Database;
use Drupal\Core\Cache\Cache;

/**
 * BackupDatabase abstract class.
 */
abstract class BackupDatabase {

  /**
   * The connection information for this connection object.
   *
   * @var array
   */
  protected $connectionOptions;

  /**
   * Init database backup.
   */
  public function __construct() {
    $this->connectionOptions = Database::getConnection()->getConnectionOptions();
    $this->error = FALSE;

    $config = \Drupal::config('backup_db.settings');
    $this->settings = array(
      'include-tables' => array(),
      'exclude-tables' => array(),
      'compress' => $config->get('settings.compress'),
      'no-data' => $config->get('settings.no_data'),
      'add-drop-table' => $config->get('settings.add_drop_table'),
      'single-transaction' => $config->get('settings.single_transaction'),
      'lock-tables' => $config->get('settings.lock_tables'),
      'add-locks' => $config->get('settings.add_locks'),
      'extended-insert' => $config->get('settings.extended_insert'),
      'complete-insert' => $config->get('settings.complete_insert'),
      'disable-keys' => $config->get('settings.disable_keys'),
      'where' => $config->get('settings.where'),
      'no-create-info' => $config->get('settings.no_create_info'),
      'skip-triggers' => $config->get('settings.skip_triggers'),
      'add-drop-trigger' => $config->get('settings.add_drop_trigger'),
      'routines' => $config->get('settings.routines'),
      'hex-blob' => $config->get('settings.hex_blob'),
      'databases' => $config->get('settings.databases'),
      'add-drop-database' => $config->get('settings.add_drop_database'),
      'skip-tz-utc' => $config->get('settings.skip_tz_utc'),
      'no-autocommit' => $config->get('settings.no_autocommit'),
      'default-character-set' => $config->get('settings.default_character_set'),
      'skip-comments' => $config->get('settings.skip_comments'),
      'skip-dump-date' => $config->get('settings.skip_dump_date')
    );

    $this->path = $config->get('path');
    $this->filename = $config->get('filename');
    $this->date_format = \Drupal::service('date.formatter')->format(time(), $config->get('date'));
  }

  /**
   * Clear cache tables.
   */
  public function init() {
    $module_handler = \Drupal::moduleHandler();
    $module_handler->invokeAll('cache_flush');

    // Flush drupal cache tables.
    foreach (Cache::getBins() as $service_id => $cache_backend) {
      $cache_backend->deleteAll();
    }

    // Clear all plugin caches.
    \Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();
    $this->prepare();
  }

  /**
   * Prepare export.
   */
  protected function prepare() {
    $this->exportFileRealPath();
    $this->export();
  }

  /**
   * Prepare export.
   */
  protected function export() {
    try {
      // Database connection info.
      $connection = $this->connectionOptions['driver'] . ':' . 'host=';
      $connection .= $this->connectionOptions['host'] . ';port=';
      $connection .= $this->connectionOptions['port'] . ';dbname=';
      $connection .= $this->connectionOptions['database'];

      $dump = new IMysqldump\Mysqldump(
        $connection, 
        $this->connectionOptions['username'],
        $this->connectionOptions['password'],
        $this->settings
      );

      $dump->start($this->file_location);
      $this->complete();
    }
    catch (\Exception $e) {
      $this->error = TRUE;

      \Drupal::logger('backup_db')->error('Could not perform backup, @error.', 
        array(
          '@error' => $e->getMessage()
        )
      );
    }
  }

  /**
   * Complete export.
   */
  abstract function complete();

  /**
   * Creates export file type and real path from URI.
   */
  protected function exportFileRealPath() {
    $path = $this->exportFilePath();
    $extension = $this->exportFileType();

    $this->file_uri = file_create_filename($this->filename . '_' . time() . $extension, $path);
    $this->file_location = \Drupal::service('file_system')->realpath($this->file_uri);
  }

  /**
   * Returns file path or false if directory cannot be created or is not writable.
   *
   * @return string or boolean
   */
  protected function exportFilePath() {
    $result = $this->path;

    if (!file_prepare_directory($this->path, FILE_CREATE_DIRECTORY)) {
      $result = FALSE;
      \Drupal::logger('backup_db')->error('The requested directory @dir could not be created.', 
        array(
          '@dir' => $this->path
        )
      );
    }
    else if (!file_prepare_directory($this->path)) {
      $result = FALSE;
      \Drupal::logger('backup_db')->error('The requested directory @dir permissions are not writable.', 
        array(
          '@dir' => $this->path
        )
      );
    }

    if ($this->date_format) {
      $filepath = $this->path . '/' . $this->date_format;
  
      if (file_prepare_directory($filepath, FILE_CREATE_DIRECTORY)) {
        $result = $filepath;
      }
    }

    return $result;
  }

  /**
   * Returns export file extension (type).
   *
   * @return string
   */
  protected function exportFileType() {
    $extension = '.sql';
    $this->content_type = 'application/octet-stream';

    switch ($this->settings['compress']) {
      case 'Gzip':
        $extension = '.gz';
        $this->content_type = 'application/gzip';
      break;
      case 'Bzip2':
        $extension = '.bz2';
        $this->content_type = 'application/x-bzip2';
    }

    return $extension;
  }

  /**
   * Error handling.
   */
  public function error() {
    return $this->error;
  }
}
