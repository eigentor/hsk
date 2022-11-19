<?php

namespace Drupal\hsk_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Block for Header Slideshow.
 *
 * @package Drupal\hsk_block\Plugin\Block
 *
 * @Block (
 *  id = "header_slideshow_block",
 *  admin_label = @Translation("Header Slideshow"),
 *  category = @Translation("HSK")
 * )
 */

class HeaderSlideshowBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $entityTypeManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition,
  EntityTypeManagerInterface $entity_type_manager)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  public function build()
  {
    $items = $this->getSlideshowItems();

    return [
      '#markup' => 'Platzhalter-Text',
    ];
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  protected function getSlideshowItems() {
    $paragraphs_ids = $this->entityTypeManager->getStorage('paragraph')->getQuery()
      ->condition('type', 'slideshow_image')
      ->execute()
      ;

    return($paragraphs_ids);
  }
}
