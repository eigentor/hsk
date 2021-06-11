<?php

namespace Drupal\spamspan\Plugin;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides common methods for Spamspan plugins.
 */
trait SpamspanSettingsFormTrait {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    // Spamspan '@' replacement.
    $element['spamspan_at'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Replacement for "@"'),
      '#default_value' => $this->getSetting('spamspan_at'),
      '#required' => TRUE,
      '#description' => $this->t('Replace "@" with this text when javascript is disabled.'),
    ];
    $element['spamspan_use_graphic'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use a graphical replacement for "@"'),
      '#default_value' => $this->getSetting('spamspan_use_graphic'),
      '#description' => $this->t('Replace "@" with a graphical representation when javascript is disabled (and ignore the setting "Replacement for @" above).'),
    ];
    $element['spamspan_dot_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Replace dots in email with text'),
      '#default_value' => $this->getSetting('spamspan_dot_enable'),
      '#description' => $this->t('Switch on dot replacement.'),
    ];
    $element['spamspan_dot'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Replacement for "."'),
      '#default_value' => $this->getSetting('spamspan_dot'),
      '#required' => TRUE,
      '#description' => $this->t('Replace "." with this text.'),
    ];

    // No trees, see https://www.drupal.org/node/2378437.
    // We fix this in our custom validate handler.
    $element['use_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Use a form instead of a link'),
      '#open' => TRUE,
    ];
    $element['use_form']['spamspan_use_form'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use a form instead of a link'),
      '#default_value' => $this->getSetting('spamspan_use_form'),
      '#description' => $this->t('Link to a contact form instead of an email address. The following settings are used only if you select this option.'),
    ];
    $element['use_form']['spamspan_form_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Replacement string for the email address'),
      '#default_value' => $this->getSetting('spamspan_form_pattern'),
      '#required' => TRUE,
      '#description' => $this->t('Replace the email link with this string and substitute the following <br />%url = the url where the form resides,<br />%email = the email address (base64 and urlencoded),<br />%displaytext = text to display instead of the email address.'),
    ];
    // Required checkbox? what is the point?
    // If needed, then make an annotation entry as well *     "spamspan_email_encode" = TRUE,
    /*$element['use_form']['spamspan_email_encode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Encode the email address'),
      '#default_value' => $this->settings['spamspan_email_encode'],
      '#required' => TRUE,
      '#description' => $this->t('Encode the email address using base64 to protect from spammers. Must be enabled for forms because the email address ends up in a URL.'),
    ];*/
    $element['use_form']['spamspan_form_default_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default url'),
      '#default_value' => $this->getSetting('spamspan_form_default_url'),
      '#required' => TRUE,
      '#description' => $this->t('Default url to form to use if none specified (e.g. me@example.com[custom_url_to_form])'),
    ];
    $element['use_form']['spamspan_form_default_displaytext'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default displaytext'),
      '#default_value' => $this->getSetting('spamspan_form_default_displaytext'),
      '#required' => TRUE,
      '#description' => $this->t('Default displaytext to use if none specified (e.g. me@example.com[custom_url_to_form|custom_displaytext])'),
    ];

    // We need this to insert our own validate/submit handlers.
    // We use our own validate handler to extract use_form settings
    $element['#process'] = [
      [$this, 'processSettingsForm'],
    ];

    return $element;
  }

  /**
   * Returns the value of a setting, or its default value if absent.
   *
   * We need to define this method because EmailSpamspanFormatter and
   * FilterSpamspan have different interfaces and FilterSpamspan is missing
   * getSetting() definition.
   * Also for what ever reason because this is a Trait method overloading does
   * not work.
   *
   * @param string $key
   *   The setting name.
   *
   * @return mixed
   *   The setting value.
   *
   * @see PluginSettingsBase::getSetting().
   */
  public function getSetting($key) {
    // Merge defaults if we have no value for the key.
    if (method_exists($this, 'mergeDefaults') && !$this->defaultSettingsMerged && !array_key_exists($key, $this->settings)) {
      $this->mergeDefaults();
    }
    return isset($this->settings[$key]) ? $this->settings[$key] : NULL;
  }

  /**
   * Attach our validation.
   */
  public function processSettingsForm(&$element, FormStateInterface $form_state, &$complete_form) {
    $complete_form['#validate'][] = [$this, 'validateSettingsForm'];
    return $element;
  }

  /**
   * Validate settings form.
   */
  public function validateSettingsForm(array &$form, FormStateInterface $form_state) {
    $settings = $form_state->getValue(['filters', 'filter_spamspan', 'settings']);
    $use_form = $settings['use_form'];

    // No trees, see https://www.drupal.org/node/2378437.
    unset($settings['use_form']);
    $settings += $use_form;
    $form_state->setValue(['filters', 'filter_spamspan', 'settings'], $settings);
  }

}
