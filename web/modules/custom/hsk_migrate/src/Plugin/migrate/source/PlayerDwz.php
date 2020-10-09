<?php

namespace Drupal\hsk_migrate\Plugin\migrate\source;

use Drupal\migrate_plus\Plugin\source\Url;
use Drupal\migrate\Row;
use Drupal\Core\Database\Database;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
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

  /**
   * PlayerDwz constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   * @param \GuzzleHttp\ClientInterface $http_client
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, ClientInterface $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->httpClient = $http_client;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('http_client'),
    );
  }

  /**
   * {@inheritdoc}
   */
  function prepareRow(Row $row) {
//    $source = $row->getSource();
//    $source_id = $source['id'];


    return parent::prepareRow($row);
  }

}

