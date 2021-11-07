<?php

namespace Drupal\hsk_news\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Provides a block that shows the picture and description
 * for a news author next to the news article.
 *
 * @Block (
 *   id = "hsk_news_author",
 *   admin_label = @Translation("News Author"),
 *   category = @Translation("News"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class NewsAuthorBlock extends BlockBase implements ContainerFactoryPluginInterface
{
  /**
   * The user object.
   * @var UserStorageInterface
   */
  protected $account;

  protected $userStorage;

  protected $entityTypeManager;

  /*
   * @inheritdoc
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $account, UserStorageInterface $user_storage, EntityTypeManagerInterface $entity_type_manager)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->account = $account;
    $this->userStorage = $user_storage;
    $this->entityTypeManager = $entity_type_manager;
  }

  /*
   * @inheritdoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static (
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('entity_type.manager')
    );
  }

  public function build()
  {
    $data = $this->getUserData();
    $peter = 7;
    return [
      '#markup' => 'Print author image and description',
    ];
  }

  public function getUserData()
  {
    $nid = $this->getContextValue('node')->id();
    $news_entity = $this->entityTypeManager->getStorage('node')->load($nid);
    $uid = $news_entity->getOwnerId();
    $peter = 7;
    return $news_entity;
  }


}
