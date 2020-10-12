<?php

namespace Drupal\hsk_migrate\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test-Block
 *
 * @Block (
 *   id = "hsk_test_block",
 *   admin_label = @Translation ("HSK Test Block"),
 *   category = @Translation ("HSK")
 *   )
 */

class HskTestBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * An http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * HskTestBlock constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \GuzzleHttp\ClientInterface $http_client
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientInterface $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $client = $this->httpClient;

    try {

      $request = $client->request('GET', 'https://www.schachbund.de/php/dewis/verein.php?zps=70107&format=xml');
      $response = $request->getBody();
      $parsed_response = (simplexml_load_string($response->__toString()));
      foreach($parsed_response->Spieler as $player) {
        if($player->vorname == "Dennes") {
          ksm($player->fideelo);
        }
      }

    } catch (RequestException $e) {
      watchdog_exception('wg_migrate', $e->getMessage());
    }

    return [
      '#markup' => 'Dies ist nur ein Test'
    ];
  }
}
