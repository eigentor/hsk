<?php

namespace Drupal\hsk_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Block for top paragraph field of Node Type "page".
 *
 * @package Drupal\hsk_block\Plugin\Block
 *
 * @Block (
 *  id = "page_paragraph_top",
 *  admin_label = @Translation("Page Top Paragraphs"),
 *  category = @Translation("HSK")
 * )
 */

class PageParagraphFieldTopBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $entityTypeManager;

  protected $routeMatch;

  public function __construct(array $configuration, $plugin_id, $plugin_definition,
                              EntityTypeManagerInterface $entity_type_manager,
                              RouteMatchInterface $route_match)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match')
    );
  }

  public function build()
  {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->routeMatch->getParameter('node');

    $peter = 7;

    if (!($node instanceof NodeInterface)) {
      return [];
    }

    // Return an empty render array if we don't have the paragraph type we want.
    $build = [];

    $builder = $this->entityTypeManager->getViewBuilder('paragraph');
    $view_mode = 'default';



    if(!empty ($node->field_gallery_above_body)) {
      $paragraphs = $node->field_gallery_above_body->referencedEntities();
//      foreach($paragraphs->referencedEntities() as $key => $paragraph) {
//        if($paragraph->getType() == 'infoblock') {
//          $build['#paragraphs'] = $paragraph;
//        }
//      }
      $paragraphs_items = $builder->viewMultiple($paragraphs, $view_mode);
      $build['#paragraphs'] = $paragraphs_items;


    }
    $build['#theme'] = 'page_paragraph_top_block';

    // Build block render array here.

    return $build;
  }
}
