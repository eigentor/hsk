<?php

namespace Drupal\spamspan\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\spamspan\SpamspanDomTrait;
use Drupal\spamspan\SpamspanInterface;
use Drupal\spamspan\SpamspanSettingsFormTrait;
use Drupal\spamspan\SpamspanTrait;

/**
 * Provides a filter to obfuscate email addresses.
 *
 * @Filter(
 *   id = "filter_spamspan",
 *   title = @Translation("SpamSpan email address encoding filter"),
 *   description = @Translation("Attempt to hide email addresses from spam-bots."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   settings = {
 *     "spamspan_at" = " [at] ",
 *     "spamspan_use_graphic" = 0,
 *     "spamspan_dot_enable" = 0,
 *     "spamspan_dot" = " [dot] ",
 *     "spamspan_use_form" = 0,
 *     "spamspan_form_pattern" = "<a href=""%url?goto=%email"">%displaytext</a>",
 *     "spamspan_form_default_url" = "contact",
 *     "spamspan_form_default_displaytext" = "contact form",
 *     "spamspan_parse_dom" = 0
 *   }
 * )
 */
class FilterSpamspan extends FilterBase implements SpamspanInterface {

  use SpamspanTrait;
  use SpamspanDomTrait;
  use SpamspanSettingsFormTrait;

  /**
   * Inline images.
   */
  const PATTERN_IMG_INLINE = '/data\:(?:.+?)base64(?:.+?)(?=["|\'])/';

  const PATTERN_IMG_PLACEHOLDER = '__spamspan_img_placeholder__';

  /**
   * If text was altered by this filter, set variable below to TRUE.
   *
   * @var bool
   */
  protected $textAltered = FALSE;

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Each email address will be obfuscated in a human readable fashion or, if JavaScript is enabled, replaced with a spam resistent clickable link. Email addresses will get the default web form unless specified. If replacement text (a persons name) is required a webform is also required. Separate each part with the "|" pipe symbol. Replace spaces in names with "_".');
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $this->textAltered = FALSE;

    // HTML image tags need to be handled separately, as they may contain base64
    // encoded images slowing down the email regex function.
    // Therefore, remove all image contents and add them back later.
    // See https://drupal.org/node/1243042 for details.
    $images = [[]];
    preg_match_all(self::PATTERN_IMG_INLINE, $text, $images);
    $text = preg_replace(
      self::PATTERN_IMG_INLINE,
      self::PATTERN_IMG_PLACEHOLDER,
      $text
    );

    if (!empty($this->settings['spamspan_parse_dom'])) {
      $text = $this->processAsDom($text, $this->textAltered);
    }
    else {
      $text = $this->processAsText($text, $this->textAltered);
    }

    // Revert back to the original image contents.
    foreach ($images[0] as $image) {
      $text = preg_replace(
        '/' . self::PATTERN_IMG_PLACEHOLDER . '/',
        $image,
        $text,
        1
      );
    }

    $result = new FilterProcessResult($text);

    if ($this->textAltered) {
      $result->addAttachments([
        'library' => [
          'spamspan/obfuscate',
        ],
      ]);

      if ($this->settings['spamspan_use_graphic']) {
        $result->addAttachments([
          'library' => [
            'spamspan/atsign',
          ],
        ]);
      }
    }

    return $result;
  }

  /**
   * Replaces email addresses using regex.
   *
   * @param string $text
   *   Input text.
   * @param bool $altered
   *   Set to true if any replacements happen.
   *
   * @return string
   *   Output text.
   */
  protected function processAsText($text, &$altered) {
    $text = $this->replaceMailtoLinks($text, $altered);
    if (!empty($this->settings['spamspan_use_form'])) {
      $text = $this->replaceEmailAddressesWithOptions($text, $altered);
    }
    $text = $this->replaceBareEmailAddresses($text, $altered);

    return $text;
  }

}
