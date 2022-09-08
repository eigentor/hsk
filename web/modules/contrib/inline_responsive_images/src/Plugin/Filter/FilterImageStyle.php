<?php

namespace Drupal\inline_responsive_images\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to render inline images as image styles.
 *
 * @Filter(
 *   id = "filter_imagestyle",
 *   module = "inline_responsive_images",
 *   title = @Translation("Display image styles"),
 *   description = @Translation("Uses the data-image-style attribute on &lt;img&gt; tags to display image styles."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class FilterImageStyle extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ImageFactory $image_factory, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->imageFactory = $image_factory;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('image.factory'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $image_styles = $this->entityTypeManager->getStorage('image_style')->loadMultiple();
    $form['image_styles'] = [
      '#type' => 'markup',
      '#markup' => 'Select the image styles that are available in the editor',
    ];
    foreach ($image_styles as $image_style) {
      $form['image_style_' . $image_style->id()] = [
        '#type' => 'checkbox',
        '#title' => $image_style->label(),
        '#default_value' => $this->settings['image_style_' . $image_style->id()] ?? 0,
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    if (stristr($text, 'data-image-style') !== FALSE) {
      $image_styles = $this->entityTypeManager->getStorage('image_style')->loadMultiple();

      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);
      foreach ($xpath->query('//*[@data-entity-type="file" and @data-entity-uuid and @data-image-style]') as $node) {
        $file_uuid = $node->getAttribute('data-entity-uuid');
        $image_style_id = $node->getAttribute('data-image-style');

        // If the image style is not a valid one, then don't transform the HTML.
        if (empty($file_uuid) || !isset($image_styles[$image_style_id])) {
          continue;
        }

        // Retrieved matching file in array for the specified uuid.
        $matching_files = $this->entityTypeManager->getStorage('file')->loadByProperties(['uuid' => $file_uuid]);
        $file = reset($matching_files);

        // Stop further element processing, if it's not a valid file.
        if (!$file) {
          continue;
        }

        $image = $this->imageFactory->get($file->getFileUri());

        // Stop further element processing, if it's not a valid image.
        if (!$image->isValid()) {
          continue;
        }

        $width = $image->getWidth();
        $height = $image->getHeight();

        $node->removeAttribute('width');
        $node->removeAttribute('height');
        $node->removeAttribute('src');

        // Make sure all non-regenerated attributes are retained.
        $attributes = [];
        for ($i = 0; $i < $node->attributes->length; $i++) {
          $attr = $node->attributes->item($i);
          $attributes[$attr->name] = $attr->value;
        }

        // Set up image render array.
        $image = [
          '#theme' => 'image_style',
          '#uri' => $file->getFileUri(),
          '#width' => $width,
          '#height' => $height,
          '#attributes' => $attributes,
          '#style_name' => $image_style_id,
        ];

        $altered_html = $this->renderer->render($image);

        // Load the altered HTML into a new DOMDocument and retrieve the
        // elements.
        $alt_nodes = Html::load(trim($altered_html))->getElementsByTagName('body')
          ->item(0)
          ->childNodes;

        foreach ($alt_nodes as $alt_node) {
          // Import the updated node from the new DOMDocument into the original
          // one, importing also the child nodes of the updated node.
          $new_node = $dom->importNode($alt_node, TRUE);
          // Add the image node(s)!
          // The order of the children is reversed later on, so insert them in
          // reversed order now.
          $node->parentNode->insertBefore($new_node, $node);
        }
        // Finally, remove the original image node.
        $node->parentNode->removeChild($node);
      }

      return new FilterProcessResult(Html::serialize($dom));
    }

    return new FilterProcessResult($text);
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      $image_styles = $this->entityTypeManager->getStorage('image_style')->loadMultiple();
      $list = array_reduce($image_styles, function ($build, $image_style) {
        $build[] = [
          '#type' => 'html_tag',
          '#tag' => 'code',
          '#value' => $image_style->id(),
        ];
        $build[] = [
          '#plain_text' => ', ',
        ];
        return $build;
      }, []);
      if (count($list) > 0) {
        // Remove the last comma.
        array_pop($list);
      }
      $list = $this->renderer->render($list);
      return $this->t('
        <p>You can display images using a site-wide style by adding a <code>data-image-style</code> attribute, whose value is one of the image style machine names: @image-style-machine-name-list.</p>', ['@image-style-machine-name-list' => $list]);
    }
    else {
      return $this->t('You can display images using site-wide styles by adding a data-image-style attribute.');
    }
  }

}
