<?php

namespace Drupal\config_default_image\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ConfigDefaultImageFormatterTrait trait.
 *
 * Unfortunately, plugins cannot be decorated. So we need a subclass for each
 * image formatter plugin, with quite the same code. So we use a trait.
 */
trait ConfigDefaultImageFormatterTrait {

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   *
   * @see https://www.previousnext.com.au/blog/safely-extending-drupal-8-plugin-classes-without-fear-of-constructor-changes
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->fileSystem = $container->get('file_system');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'default_image' => [
        'path' => '',
        'use_image_style' => FALSE,
        'alt' => '',
        'title' => '',
        'width' => NULL,
        'height' => NULL,
      ],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\image\Plugin\Field\FieldType\ImageItem
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $settings = $this->getSettings();

    $element['default_image'] = [
      '#type' => 'details',
      '#title' => t('Default image'),
      '#open' => TRUE,
      '#required' => TRUE,
    ];
    $element['default_image']['path'] = [
      '#type' => 'textfield',
      '#title' => t('Image path'),
      '#description' => t('Drupal path to the image to be shown if no image is uploaded (the image would typically be in a git-managed directory so that it can be deployed easily). Example: /themes/custom/my_theme/img/default_image.jpg'),
      '#default_value' => $settings['default_image']['path'],
      '#required' => TRUE,
      // TODO validate path.
    ];
    $element['default_image']['use_image_style'] = [
      '#type' => 'checkbox',
      '#title' => t('Apply the image style'),
      '#description' => t('Check this box to use the image style on the default image'),
      '#default_value' => $settings['default_image']['use_image_style'],
    ];
    $element['default_image']['alt'] = [
      '#type' => 'textfield',
      '#title' => t('Alternative text'),
      '#description' => t('This text will be used by screen readers, search engines, and when the image cannot be loaded.'),
      '#default_value' => $settings['default_image']['alt'],
      '#maxlength' => 512,
    ];
    $element['default_image']['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#description' => t('The title attribute is used as a tooltip when the mouse hovers over the image.'),
      '#default_value' => $settings['default_image']['title'],
      '#maxlength' => 1024,
    ];
    $element['default_image']['width'] = [
      '#type' => 'value',
      '#value' => $settings['default_image']['width'],
    ];
    $element['default_image']['height'] = [
      '#type' => 'value',
      '#value' => $settings['default_image']['height'],
    ];
    $element['default_image']['#description'] = t('If no image is set for the field (not even a field-level default image), this image will be shown on display.');

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = t('Fallback to a default image');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    if (empty($elements)) {
      $default_image = $this->getSetting('default_image');
      $image_path = $default_image['path'];
      if (!empty($image_path)) {
        if ($default_image['use_image_style']) {
          // $image_path must be ready for
          // Drupal\image\Entity\ImageStyle::buildUri().
          // This needs a valid scheme.
          // As long as https://www.drupal.org/project/drupal/issues/1308152 is
          // not fixed, files stored outside from public, private and temporary
          // directories have no scheme.
          // So that if our path has no scheme, we copy the file to the public
          // files directory and add it as scheme.
          if (!StreamWrapperManager::getScheme($image_path)) {
            $image_path = ltrim($image_path, '/');
            $destination = 'public://config_default_image/' . $image_path;
            $directory = $this->fileSystem->dirname($destination);
            $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
            if (!file_exists($destination)) {
              $image_path = $this->fileSystem->copy($image_path, $destination);
            }
            else {
              $image_path = $destination;
            }
          }
        }
        else {
          $this->setSetting('image_style', FALSE);
        }

        $file = File::create([
          'uid' => 0,
          'filename' => $this->fileSystem->basename($image_path),
          'uri' => $image_path,
          'status' => 1,
        ]);

        /* @see \Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase::getEntitiesToView() */
        // Clone the FieldItemList into a runtime-only object for the formatter,
        // so that the fallback image can be rendered without affecting the
        // field values in the entity being rendered.
        $items = clone $items;
        $items->setValue([
          'target_id' => $file->id(),
          'alt' => $default_image['alt'],
          'title' => $default_image['title'],
          'width' => $default_image['width'],
          'height' => $default_image['height'],
          'entity' => $file,
          '_loaded' => TRUE,
          '_is_default' => TRUE,
        ]);
        $file->_referringItem = $items[0];

        // For maximum compatibility with other modules such as SVG Image, we
        // call the parent image formatter with our items instead of
        // reimplementing the viewElements() code.
        $elements = parent::viewElements($items, $langcode);
      }

    }
    return $elements;
  }

}
