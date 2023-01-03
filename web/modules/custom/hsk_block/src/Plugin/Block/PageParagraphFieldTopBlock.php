<?php

namespace Drupal\hsk_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Cache\Cache;

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

    if (!($node instanceof NodeInterface)) {
      return [];
    }

    // Return an empty render array if we don't have the paragraph type we want.
    $build = [];

    // Get the viewBuilder to prepare our paragraphs for the render array
    $builder = $this->entityTypeManager->getViewBuilder('paragraph');
    $view_mode = 'infoblock';

    // Get all paragraphs of type "infoblock" from the paragraphs reference field
    // "field_gallery_above_body" from the node and render them in the block.
    // Do not render other paragraph types.
    if(!empty ($node->field_gallery_above_body)) {
      $paragraphs = $node->field_gallery_above_body->referencedEntities();
      foreach($paragraphs as $key => $paragraph) {
        if($paragraph->getType() == 'infoblock') {
          $paragraphs_infoblocks[$key] = $paragraph;
        }
      }

      if(!empty($paragraphs_infoblocks)) {
      $paragraphs_items = $builder->viewMultiple($paragraphs_infoblocks, $view_mode);
      $build['#paragraphs'] = $paragraphs_items;
        $build['#theme'] = 'page_paragraph_top_block';
      }
    }

    return $build;
  }

  /**
   * Set Cachetags
   *
   * @return array|string[]
   */
  public function getCacheTags() {
    // With this when your node change your block will rebuild.
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      // If there is node add its cachetag.
      return Cache::mergeTags(parent::getCacheTags(), ['node:' . $node->id()]);
    }
    else {
      // Return default tags instead.
      return parent::getCacheTags();
    }
  }

  /**
   * Set cache context
   *
   * @return array|string[]
   */
  public function getCacheContexts() {
    // Every new route this block will rebuild.
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }
}
