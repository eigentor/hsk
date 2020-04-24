<?php

namespace Drupal\better_formats;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions of the better formats module.
 */
class BetterFormatsPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new BetterFormatsPermissions instance.
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

  /**
   * Returns an array of better formats permissions.
   *
   * @return array
   */
  public function permissions() {
    $permissions = [];

    // Generate permissions for each entity type.
    foreach ($this->entityManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type->get('field_ui_base_route')) {
        $permissions['hide format selection for ' . $entity_type_id] = [
          'title' => $this->t('Hide format selection for @label', ['@label' => $entity_type->getLabel()]),
        ];
      }
    }

    return $permissions;
  }

}
