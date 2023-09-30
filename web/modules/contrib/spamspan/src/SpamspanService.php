<?php

namespace Drupal\spamspan;

use Drupal\filter\FilterPluginManager;

/**
 * Spamspan Service class.
 *
 * @package Drupal\spamspan
 */
class SpamspanService {
  /**
   * Filter manager.
   *
   * @var \Drupal\filter\FilterPluginManager
   */
  protected $filterManager;

  /**
   * Constructs a new SpamspanService.
   *
   * @param \Drupal\filter\FilterPluginManager $filter_manager
   *   The filter plugin manager.
   */
  public function __construct(FilterPluginManager $filter_manager) {
    $this->filterManager = $filter_manager;
  }

  /**
   * Run text through FilterSpamspan.
   *
   * @param string $text
   *   Text, maybe containing email addresses.
   * @param array $settings
   *   An associative array of settings to be applied.
   *   Overrides default settings.
   *
   * @return string
   *   The input text with emails replaced by spans
   */
  public function spamspan($text, array $settings = []) {
    $configuration = $this->filterManager->getDefinition('filter_spamspan');
    $configuration['settings'] = $settings + $configuration['settings'];

    $filter = $this->filterManager->createInstance('filter_spamspan', $configuration);

    return $filter->process($text, NULL)->getProcessedText();
  }

}
