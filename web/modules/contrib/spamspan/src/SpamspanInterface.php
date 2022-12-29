<?php

namespace Drupal\spamspan;

/**
 * Spamspan interface.
 */
interface SpamspanInterface {

  /**
   * Special characters in the local part.
   */
  const PATTERN_SPECIAL = '\-\.\~\'\!\#$\%\&\+\/\*\=\?\^\_\`\{\|\}\+^@';

  /**
   * Set up a regex constant to split an email into name and domain parts.
   *
   * The following pattern is not perfect (who is?), but is intended to
   * intercept things which look like email addresses.  It is not intended to
   * determine if an address is valid.  It will not intercept addresses with
   * quoted local parts.
   */
  const PATTERN_MAIN =
    // Group 1 - the local part: dash, dot or special characters.
    '([' . self::PATTERN_SPECIAL . '\w]+)@'
    // Group 2 - the domain.
    . '((?:'
    // One or more letters or dashes followed by a dot.
    . '[-\w]+\.'
    // One or more times.
    . ')+'
    // Between 2 and 63 letters at the end (new TLDs).
    . '[A-Z]{2,63})';

  const PATTERN_EMAIL_BARE = '!' . self::PATTERN_MAIN . '!ix';

  /**
   * For cases when spamspan_use_form is checked.
   *
   * Example: user@example.museum[mycontactform|Contact me using this form].
   */
  const PATTERN_EMAIL_WITH_OPTIONS = '!' . self::PATTERN_MAIN . '\[(.*?)\]!ix';

  /**
   * Regex for mailto URLs.
   *
   * See http://www.faqs.org/rfcs/rfc2368.html.
   * This captures the whole mailto URL into the second group,
   * the name into the third group and the domain into the fourth.
   * Attributes before href go into first group and the ones after into fifth.
   * The tag contents go into the sixth.
   */
  const PATTERN_MAILTO =
    // Opening <a and spaces.
    '!<a\s+'
    // Any attributes before href.
    . "((?:(?:[\w|-]+\s*=\s*)(?:\w+|\"[^\"]*\"|'[^']*')\s*)*?)"
    // The href attribute.
    . "href\s*=\s*(['\"])\s*(mailto:"
    // The email address.
    . self::PATTERN_MAIN
    // An optional ? followed by a query string.
    // We allow spaces here, even though strictly they should be URL encoded.
    . "(?:\?[A-Za-z0-9_= %\.\-\~\_\&;\!\*\(\)\\'#&]*)?\s*)"
    // The relevant quote character.
    . '\\2'
    // Any more attributes after href.
    . "((?:(?:\s+[\w|-]+\s*=\s*)(?:\w+|\"[^\"]*\"|'[^']*'))*?)"
    // End of the opening tag.
    . '>'
    // Tag contents.
    // This will not work properly if there is a nested <a>,
    // but this is not valid xhtml anyway.
    . '(.*?)'
    // Closing tag.
    . '</a>!ixs';

  /**
   * The list of HTML tags allowed.
   */
  const ALLOWED_HTML = ['abbr', 'acronym', 'address', 'article', 'aside', 'b', 'bdi', 'bdo', 'big', 'blockquote', 'br', 'caption', 'cite', 'code', 'col', 'colgroup', 'command', 'dd', 'del', 'details', 'dfn', 'div', 'dl', 'dt', 'em', 'figcaption', 'figure', 'footer', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'hr', 'i', 'img', 'ins', 'kbd', 'li', 'mark', 'menu', 'meter', 'nav', 'ol', 'output', 'p', 'pre', 'progress', 'q', 'rp', 'rt', 'ruby', 's', 'samp', 'section', 'small', 'span', 'strong', 'sub', 'summary', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'time', 'tr', 'tt', 'u', 'ul', 'var', 'wbr', '!--', 'svg', 'animate', 'title', 'use', 'g', 'text', 'textPath', 'tspan', 'symbol', 'defs', 'desc', 'mask', 'marker', 'mpath', 'path', 'polygon', 'polyline', 'circle', 'clipPath', 'ellipse', 'line', 'radialGradient', 'rect', 'image', 'linearGradient'];

  /**
   * Obfuscation based on PATTERN_EMAIL_BARE.
   *
   * @param string $text
   *   Text obfuscate.
   * @param bool $altered
   *   Sets this to true if any obfuscation occurred.
   *
   * @return string
   *   The obfuscated text.
   */
  public function replaceBareEmailAddresses($text, &$altered = NULL);

  /**
   * Obfuscation based on PATTERN_EMAIL_WITH_OPTIONS.
   *
   * @param string $text
   *   Text obfuscate.
   * @param bool $altered
   *   Sets this to true if any obfuscation occurred.
   *
   * @return string
   *   The obfuscated text.
   */
  public function replaceEmailAddressesWithOptions($text, &$altered = NULL);

  /**
   * Obfuscation based on PATTERN_MAILTO.
   *
   * @param string $text
   *   Text obfuscate.
   * @param bool $altered
   *   Sets this to true if any obfuscation occurred.
   *
   * @return string
   *   The obfuscated text.
   */
  public function replaceMailtoLinks($text, &$altered = NULL);

}
