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



    $peter = 7;

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

    // There might be multiple "slideshow" paragraph items, so we load them all
    $slideshow_parent_item = $this->entityTypeManager->getStorage('paragraph')->getQuery()
      ->condition('type', 'slideshow')
      ->execute()
    ;

    // load the full paragraph entities for the slideshow items
    $parent_items = $this->entityTypeManager->getStorage('paragraph')->loadMultiple($slideshow_parent_item);

    // We write "slideshow_image" paragraphs that are referenced on the "field_sl_image" field inside
    // the slideshow items into an array that is keyed by the "slideshow" paragraph ids.
    // This way we stay flexible if in the future there might be multiple "slideshow" paragraph items
    $slideshow_items = [];
    foreach($parent_items as $parent_item) {
      $paragraph_id = $parent_item->id();
      $children = $parent_item->get('field_sl_image')->referencedEntities();
      $slideshow_items[$paragraph_id] = $children;
    }

    // But now we are pragmatic: we only load the "slideshow_image" items from the first "slideshow" item.
    $selected_items = reset($slideshow_items);

    return($selected_items);
  }
}
