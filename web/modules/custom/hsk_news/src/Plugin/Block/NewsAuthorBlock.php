<?php

namespace Drupal\hsk_news\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\UserStorageInterface;

/**
 * Provides a block that shows the picture and description
 * for a news author next to the news article.
 *
 * @Block (
 *   id = "hsk_news_author",
 *   admin_label = @Translation("News Author")
 * )
 */

class NewsAuthorBlock extends BlockBase implements ContainerFactoryPluginInterface
{
  /**
   * The user object.
   */
  protected $account;

  protected $userStorage;

  /*
   * @inheritdoc
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->account = $account;
    $this->userStorage = $user_storage;
  }

  /*
   * @inheritdoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static (
    $container,
    $configuration,
    $plugin_id,
    $container->get('current_user'),
    $container->get('entity_type.manager')->getStorage('user');
    );
  }

  public function build()
  {
    return [
      '#markup' => 'Print author image and description',
    ];
  }


}
