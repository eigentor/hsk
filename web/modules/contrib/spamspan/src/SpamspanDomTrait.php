<?php

namespace Drupal\spamspan;

/**
 * Trait SpamspanDomTrait.
 *
 * @package Drupal\spamspan
 *
 * Create DOMDocument from text with loadHTML.
 *
 * Scan for mailto links only in <a> nodes.
 * Scan for email addresses only in text nodes.
 *
 * Note: Using DOM will alter text in unexpected ways, besides obfuscation.
 */
trait SpamspanDomTrait {

  /**
   * Replaces email addresses using DOM and regex.
   *
   * @param string $text
   *   Input text.
   * @param bool $altered
   *   Set to true if any replacements happen.
   *
   * @return string
   *   Output text.
   */
  protected function processAsDom($text, &$altered) {
    $document = $this->loadHtmlDocument($text);

    // Process mailto <a> tags.
    foreach ($document->getElementsByTagName('a') as $atag) {
      $href = trim($atag->getAttribute('href'));
      if (strpos($href, 'mailto:') === 0) {
        $atag->setAttribute('href', $href);

        $text = $this->replaceMailtoLinks($document->saveHTML($atag), $altered);
        $this->replaceDomNode($atag, $text);
      }
    }

    // Parse all text nodes.
    $xpath = new \DomXPath($document);

    foreach ($xpath->query('//text()') as $text_node) {
      $text = $text_node->nodeValue;
      $node_altered = FALSE;

      if (!empty($this->settings['spamspan_use_form'])) {
        $text = $this->replaceEmailAddressesWithOptions($text, $node_altered);
      }
      $text = $this->replaceBareEmailAddresses($text, $node_altered);

      if ($node_altered) {
        $this->replaceDomNode($text_node, $text);
        $altered = TRUE;
      }
    }

    return $this->toStringHtmlDocument($document);
  }

  /**
   * Replace DOM node with another one created from text.
   *
   * @param \DOMNode $old_node
   *   Node to be replaced.
   * @param string $new_text
   *   Html for new node.
   */
  protected function replaceDomNode(\DOMNode $old_node, $new_text) {
    $fragment = $this->loadHtmlDocument($new_text);
    $div = $fragment->getElementsByTagName('div')->item(0);

    foreach ($div->childNodes as $child) {
      $new_node = $old_node->ownerDocument->importNode($child, TRUE);
      $old_node->parentNode->insertBefore($new_node, $old_node);
    }

    $old_node->parentNode->removeChild($old_node);
  }

  /**
   * Load text as DOM Document.
   *
   * @param string $text
   *   Text to load.
   *
   * @return \DOMDocument
   *   DOM Document.
   */
  protected function loadHtmlDocument($text) {
    $document = new \DOMDocument();
    // Replace CRLF with LF, because CR will be encoded as &#13 otherwise.
    $text = str_replace("\r\n", "\n", $text);

    // Ignore warnings with '@' due to unknown HTML5 tags (section, aside, etc).
    @$document->loadHTML(
      '<?xml encoding="UTF-8"><div>' . $text . '</div>',
      LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    return $document;
  }

  /**
   * Convert DOM Document back to html.
   *
   * @param \DOMDocument $document
   *   DOM Document.
   *
   * @return string
   *   Html.
   */
  protected function toStringHtmlDocument(\DOMDocument $document) {
    foreach ($document->childNodes as $item) {
      if ($item->nodeType == XML_PI_NODE) {
        $document->removeChild($item);
        break;
      }
    }
    // Use saveHTML(documentElement) instead of simply saveHTML() to prevent
    // utf-8 characters (e.g. accented letters) from being turned into entities
    // At the same time, this will strip the DOCTYPE.
    $div = $document->getElementsByTagName("div")->item(0);

    return implode(
      '',
      array_map([$document, 'saveHTML'], iterator_to_array($div->childNodes))
    );
  }

}
