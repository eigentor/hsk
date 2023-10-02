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
 * Plugin implementation of the 'hms_natural_language_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "hms_natural_language_formatter",
 *   label = @Translation("Natural language"),
 *   field_types = {
 *     "hms"
 *   }
 * )
 */
class HMSNaturalLanguageFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

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
      'display_formats' => ["w", "d", "h", "m", "s"],
      'separator' => ", ",
      "last_separator" => " and ",
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $options = [];
    $factors = $this->hms_service->factorMap(TRUE);
    $order = $this->hms_service->factorMap();
    arsort($order, SORT_NUMERIC);
    foreach ($order as $factor => $info) {
      $options[$factor] = $factors[$factor]['label multiple'];
    }
    $elements['display_formats'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Display fragments'),
      '#options' => $options,
      '#description' => $this->t('Formats that are displayed in this field'),
      '#default_value' => $this->getSetting('display_formats'),
      '#required' => TRUE,
    ];
    $elements['separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Separator'),
      '#description' => $this->t('Separator used between fragments'),
      '#default_value' => $this->getSetting('separator'),
      '#required' => TRUE,
    ];
    $elements['last_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last separator'),
      '#description' => $this->t('Separator used between the last 2 fragments'),
      '#default_value' => $this->getSetting('last_separator'),
      '#required' => FALSE,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $factors = $this->hms_service->factorMap(TRUE);
    $fragments = $this->getSetting('display_formats');
    $fragment_list = [];
    foreach ($fragments as $fragment) {
      if ($fragment) {
        $fragment_list[] = $factors[$fragment]['label multiple'];
      }
    }
    $summary[] = $this->t('Displays: @display', ['@display' => implode(', ', $fragment_list)]);
    $summary[] = $this->t("Separator: '@separator'", ['@separator' => $this->getSetting('separator')]);
    if (strlen($this->getSetting('last_separator'))) {
      $summary[] = $this->t("Last Separator: '@last_separator'", ['@last_separator' => $this->getSetting('last_separator')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta]['#theme'] = 'hms_natural_language';
      $element[$delta]['#value'] = $item->value;
      $element[$delta]['#format'] = '';
      foreach ($this->getSetting('display_formats') as $fragment) {
        if ($fragment) {
          $element[$delta]['#format'] .= ':' . $fragment;
        }
      }
      if (!strlen($element[$delta]['#format'])) {
        $element[$delta]['#format'] = implode(':', array_keys($this->hms_service->factorMap(TRUE)));
      }
      else {
        $element[$delta]['#format'] = substr($element[$delta]['#format'], 1);
      }
      $element[$delta]['#separator'] = $this->getSetting('separator');
      $element[$delta]['#last_separator'] = $this->getSetting('last_separator');
    }

    return $element;
  }

}
