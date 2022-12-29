<?php

namespace Drupal\spamspan;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;

/**
 * Trait SpamspanTrait.
 *
 * @package Drupal\spamspan
 *
 * Implements core spamspan logic of detecting and obfuscating email addresses.
 *
 * We are aiming to replace emails with code like this:
 *   <span class="spamspan">
 *     <span class="u">user</span>
 *     [at]
 *     <span class="d">example [dot] com</span>
 *     <span class="t">tag contents</span>
 *   </span>
 *
 * @property array settings
 * @method $this t($string, array $args = [], array $options = [])
 */
trait SpamspanTrait {

  /**
   * {@inheritDoc}
   */
  public function replaceBareEmailAddresses($text, &$altered = NULL) {
    $callback = function ($matches) use (&$altered) {
      $altered = TRUE;
      return $this->callbackBareEmailAddresses($matches);
    };

    return preg_replace_callback(
      SpamspanInterface::PATTERN_EMAIL_BARE,
      $callback,
      $text
    );
  }

  /**
   * {@inheritDoc}
   */
  public function replaceEmailAddressesWithOptions($text, &$altered = NULL) {
    $callback = function ($matches) use (&$altered) {
      $altered = TRUE;
      return $this->callbackEmailAddressesWithOptions($matches);
    };

    return preg_replace_callback(
      SpamspanInterface::PATTERN_EMAIL_WITH_OPTIONS,
      $callback,
      $text
    );
  }

  /**
   * {@inheritDoc}
   */
  public function replaceMailtoLinks($text, &$altered = NULL) {
    $callback = function ($matches) use (&$altered) {
      $altered = TRUE;
      return $this->callbackMailto($matches);
    };

    return preg_replace_callback(
      SpamspanInterface::PATTERN_MAILTO,
      $callback,
      $text
    );
  }

  /**
   * Callback function for preg_replace_callback.
   *
   * Replaces mailto <a> tags.
   *
   * @param array $matches
   *   The matches from preg_match.
   *   $matches[0] - the entire matched string.
   *   $matches[1] - any attributes before href attribute.
   *   $matches[2] - the matched quote for href attribute.
   *   $matches[3] - the entire mailto href attribute value.
   *   $matches[4] - the local part of the email address.
   *   $matches[5] - the domain of the email address.
   *   $matches[6] - any attributes after href attribute.
   *   $matches[7] - the contents of <a> tag.
   *
   * @return string
   *   The replaced text.
   */
  public function callbackMailto(array $matches) {
    // Take the mailto: URL in $matches[3] and split the query string
    // into its component parts, putting them in $headers as
    // [0]=>"header=contents" etc.  We cannot use parse_str because
    // the query string might contain dots.
    // Single quote can be encoded as &#039; which breaks parse_url.
    // Replace it back to a single quote which is perfectly valid.
    $matches[3] = str_replace("&#039;", '\'', $matches[3]);
    $query = parse_url($matches[3], PHP_URL_QUERY);
    $query = isset($query) ? str_replace('&amp;', '&', $query) : '';
    $headers = preg_split('/[&;]/', $query);
    // If no matches, $headers[0] will be set to '' so $headers must be reset.
    if ($headers[0] == '') {
      $headers = [];
    }

    // Take all <a> attributes except the href and put them into custom $vars.
    $vars = $attributes = [];
    // Before href.
    if (!empty($matches[1])) {
      $matches[1] = trim($matches[1]);
      $attributes[] = $matches[1];
    }
    // After href.
    if (!empty($matches[6])) {
      $matches[6] = trim($matches[6]);
      $attributes[] = $matches[6];
    }
    if (count($attributes)) {
      $vars['extra_attributes'] = implode(' ', $attributes);
    }

    return $this->output($matches[4], $matches[5], $matches[7], $headers,
      $vars);

  }

  /**
   * Callback function for preg_replace_callback.
   *
   * Replaces user@exampl.com[mycontact|Contact me].
   *
   * @param array $matches
   *   The matches from preg_match.
   *
   *   $matches[0] - the entire matched string.
   *   $matches[1] - the local part of the email address.
   *   $matches[2] - the domain of the email address.
   *   $matches[3] - the options,
   *     i.e. the text between [] after the email address.
   *
   * @return string
   *   The replaced text.
   */
  public function callbackEmailAddressesWithOptions(array $matches) {
    $vars = [];
    if (!empty($matches[3])) {
      $options = explode('|', $matches[3]);
      if (!empty($options[0])) {
        $custom_form_url = trim($options[0]);
        if (!empty($custom_form_url)) {
          $vars['custom_form_url'] = $custom_form_url;
        }
      }
      if (!empty($options[1])) {
        $custom_displaytext = trim($options[1]);
        if (!empty($custom_displaytext)) {
          $vars['custom_displaytext'] = $custom_displaytext;
        }
      }
    }
    return $this->output($matches[1], $matches[2], '', [], $vars);
  }

  /**
   * Callback function for preg_replace_callback.
   *
   * Replaces bare email addresses.
   *
   * @param array $matches
   *   The matches from preg_match.
   *   $matches[0] - the entire matched string.
   *   $matches[1] - the local part of the email address.
   *   $matches[2] - the domain of the email address.
   *
   * @return string
   *   The replaced text.
   */
  public function callbackBareEmailAddresses(array $matches) {
    return $this->output($matches[1], $matches[2]);
  }

  /**
   * A helper function for the callbacks.
   *
   * Replace an email address which has been found with the appropriate
   * <span> tags.
   *
   * @param string $name
   *   The user name.
   * @param string $domain
   *   The email domain.
   * @param string $contents
   *   The contents of any <a> tag.
   * @param array $headers
   *   The email headers extracted from a mailto: URL.
   * @param array $vars
   *   Optional parameters. Used only when spamspan_use_form = true.
   *
   * @return string
   *   The span with which to replace the email address.
   */
  protected function output(
    $name,
    $domain,
    $contents = '',
    array $headers = [],
    array $vars = []
  ) {
    // Processing for forms.
    if (!empty($this->settings['spamspan_use_form'])) {
      return $this->outputWhenUseForm($name, $domain, $contents, $headers, $vars);
    }

    $at = $this->settings['spamspan_at'];
    if ($this->settings['spamspan_use_graphic']) {
      $render_at = [
        '#theme' => 'spamspan_at_sign',
        '#settings' => $this->settings,
      ];
      /** @var \Drupal\Core\Render\RendererInterface $renderer */
      $renderer = \Drupal::service('renderer');
      $at = $renderer->renderPlain($render_at);
    }

    if ($this->settings['spamspan_dot_enable']) {
      // Replace .'s in the address with [dot].
      $name = str_replace('.',
        '<span class="o">' . $this->settings['spamspan_dot'] . '</span>',
        $name);
      $domain = str_replace('.',
        '<span class="o">' . $this->settings['spamspan_dot'] . '</span>',
        $domain);
    }
    $output = '<span class="u">' . $name . '</span>' . $at . '<span class="d">' . $domain . '</span>';

    // Ff there are headers, include them as eg (subject: xxx, cc: zzz).
    // Replace the '=' in the headers with ': ', so it looks nicer.
    if (count($headers)) {
      foreach ($headers as $key => $header) {
        // Url encode header.
        $header = rawurlencode(rawurldecode($header));
        // Replace the first '=' sign.
        $header = preg_replace('/%3D/', ': ', $header, 1);
        $headers[$key] = $header;
      }
      $output .= '<span class="h"> (' . Html::escape(implode(', ',
          $headers)) . ') </span>';
    }

    $contents = $this->filterTagContents($contents);
    // If there are tag contents, include them between round brackets.
    if (!empty($contents)) {
      $output .= '<span class="t"> (' . $contents . ')</span>';
    }

    // Put in the extra <a> attributes.
    if (!empty($vars['extra_attributes'])) {
      $output .= '<span class="e">' . strip_tags($vars['extra_attributes']) . '</span>';
    }

    $output = '<span class="spamspan">' . $output . '</span>';

    return $output;
  }

  /**
   * Clean up the contents of <a> tag.
   *
   * Remove emails from the tag contents, otherwise the tag contents are
   * themselves converted into a spamspan, with undesirable consequences.
   * See bug #305464.
   *
   * Applies Xss::filter.
   *
   * @param string $contents
   *   The tag contents.
   *
   * @return string
   *   Cleaned up contents.
   */
  protected function filterTagContents($contents) {

    if (!empty($contents)) {
      $contents = preg_replace(SpamspanInterface::PATTERN_EMAIL_BARE, '', $contents);

      // Remove anything except certain inline elements, just in case.
      // Nested <a> elements are illegal.
      // <img> needs to be here to allow for graphic @.
      $contents = Xss::filter($contents, SpamspanInterface::ALLOWED_HTML);
    }

    return $contents;
  }

  /**
   * A version of $this->output method.
   *
   * To use when spamspan_use_form option is checked.
   */
  protected function outputWhenUseForm(
    $name,
    $domain,
    $contents = '',
    array $headers = [],
    array $vars = []
  ) {
    // Processing for forms.
    if (!empty($this->settings['spamspan_use_form'])) {
      $email = urlencode(base64_encode($name . '@' . $domain));

      // Put in the defaults if nothing set.
      if (empty($vars['custom_form_url'])) {
        $vars['custom_form_url'] = $this->settings['spamspan_form_default_url'];
      }
      if (empty($vars['custom_displaytext'])) {
        $vars['custom_displaytext'] = $this->t($this->settings['spamspan_form_default_displaytext']);
      }
      $vars['custom_form_url'] = strip_tags($vars['custom_form_url']);
      $vars['custom_displaytext'] = strip_tags($vars['custom_displaytext']);

      $url_parts = parse_url($vars['custom_form_url']);
      if (!$url_parts) {
        $vars['custom_form_url'] = '';
      }
      else {
        if (empty($url_parts['host'])) {
          $vars['custom_form_url'] = base_path() . trim($vars['custom_form_url'],
              '/');
        }
      }

      $replace = [
        '%url' => $vars['custom_form_url'],
        '%displaytext' => $vars['custom_displaytext'],
        '%email' => $email,
      ];

      return strtr($this->settings['spamspan_form_pattern'], $replace);
    }
  }

}
