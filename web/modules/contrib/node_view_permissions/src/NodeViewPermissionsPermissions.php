<?php
/**
 * @file
 * Contains \Drupal\node_view_permissions\NodeViewPermissionsPermissions.
 */

namespace Drupal\node_view_permissions;

use Drupal\Component\Utility\String;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\node\Entity\NodeType;

class NodeViewPermissionsPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;
  use UrlGeneratorTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new NodeViewPermissionsPermission instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'));
  }

  public function permissions() {
    $permissions = [];
    $nodeTypes = NodeType::loadMultiple();
    foreach ($nodeTypes as $nodeType) {
      $permission = 'view any ' . $nodeType->id() . ' content';
      $permissions[$permission] = [
        'title' => $this->t('<em>@type_label</em>: View any content', ['@type_label' => $nodeType->label()]),
      ];
      $permission = 'view own ' . $nodeType->id() . ' content';
      $permissions[$permission] = [
        'title' => $this->t('<em>@type_label</em>: View own content', ['@type_label' => $nodeType->label()]),
      ];
    }
    return $permissions;
  }
  
}
