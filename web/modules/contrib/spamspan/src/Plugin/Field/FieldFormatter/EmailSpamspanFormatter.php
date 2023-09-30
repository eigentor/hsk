<?php

namespace Drupal\spamspan\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\spamspan\SpamspanService;
use Drupal\spamspan\SpamspanSettingsFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'email_mailto' formatter.
 *
 * @FieldFormatter(
 *   id = "email_spamspan",
 *   label = @Translation("Email SpamSpan"),
 *   field_types = {
 *     "email"
 *   }
 * )
 *
 * @ingroup field_formatter
 */
class EmailSpamspanFormatter extends FormatterBase {
  use SpamspanSettingsFormTrait;

  /**
   * The Spamspan service.
   *
   * @var \Drupal\spamspan\SpamspanService
   */
  protected $spamspanService;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, SpamspanService $spamspan_service) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->spamspanService = $spamspan_service;
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
      $container->get('spamspan'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $plugin_manager = \Drupal::service('plugin.manager.filter');
    $configuration = $plugin_manager->getDefinition('filter_spamspan');

    return $configuration['settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    if ($this->getSetting('spamspan_use_form')) {
      $summary[] = $this->t('Link to a contact form instead of an email address');
    }
    else {
      $summary[] = $this->t('Replacement for "@" is %1', ['%1' => $this->getSetting('spamspan_at')]);
      if ($this->getSetting('spamspan_use_graphic')) {
        $summary[] = $this->t('Use a graphical replacement for "@"');
      }
      if ($this->getSetting('spamspan_dot_enable')) {
        $summary[] = $this->t('Replacement for "." is %1', ['%1' => $this->getSetting('spamspan_dot')]);
      }
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function validateSettingsForm(array &$form, FormStateInterface $form_state) {
    $field_name = $form_state->get('plugin_settings_edit');
    $settings = $form_state->getValue(
      ['fields', $field_name, 'settings_edit_form', 'settings']
    );
    $use_form = $settings['use_form'];

    // No trees, see https://www.drupal.org/node/2378437.
    unset($settings['use_form']);
    $settings += $use_form;
    $form_state->setValue(
      ['fields', $field_name, 'settings_edit_form', 'settings'],
      $settings
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#markup' => $this->spamspanService->spamspan($item->value, $this->getSettings()),
        '#attached' => ['library' => ['spamspan/obfuscate']],
      ];
    }

    return $elements;
  }

}
