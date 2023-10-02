<?php

namespace Drupal\hms_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\hms_field\HMSServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'hms_default_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "hms_default_formatter",
 *   label = @Translation("Hours Minutes and Seconds"),
 *   field_types = {
 *     "hms"
 *   }
 * )
 */
class HMSFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Constructor.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, HMSServiceInterface $hms_service) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->hms_service = $hms_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('hms_field.hms')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'format' => 'h:mm',
      'leading_zero' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    $elements['format'] = [
      '#type' => 'select',
      '#title' => $this->t('Display format'),
      '#options' => $this->hms_service->formatOptions(),
      '#description' => $this->t('The display format used for this field'),
      '#default_value' => $settings['format'],
      '#required' => TRUE,
    ];
    $elements['leading_zero'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Leading zero'),
      '#description' => $this->t('Leading zero values will be displayed when this option is checked'),
      '#default_value' => $settings['leading_zero'],
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();
    $summary[] = $this->t('Format: @format', ['@format' => $settings['format']]);
    $summary[] = $this->t('Leading zero: @zero', ['@zero' => ($settings['leading_zero'] ? $this->t('On') : $this->t('Off'))]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'hms',
        '#value' => $item->value,
        '#format' => $this->getSetting('format'),
        '#leading_zero' => $this->getSetting('leading_zero'),
      ];
    }

    return $element;
  }

}
