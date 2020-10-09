<?php

namespace Drupal\hsk_migrate\Plugin\migrate\source;

use Drupal\migrate_plus\Plugin\source\Url;
use Drupal\migrate\Row;
use Drupal\Core\Database\Database;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationInterface;

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
   * An http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  public function __construct(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->httpClient = $http_client;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    return new static(
      $container->get('http_client'),
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration
    );
  }

  /**
   * {@inheritdoc}
   */
  function prepareRow(Row $row) {
    $source = $row->getSource();
    $source_id = $source['id'];


    return parent::prepareRow($row);
  }

}

