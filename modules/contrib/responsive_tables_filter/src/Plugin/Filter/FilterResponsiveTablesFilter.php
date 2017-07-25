<?php

namespace Drupal\responsive_tables_filter\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Responsive Tables Filter class. Implements process() method only.
 *
 * @Filter(
 *   id = "filter_responsive_tables_filter",
 *   title = @Translation("Apply responsive behavior to HTML tables."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class FilterResponsiveTablesFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    if ($filtered = $this->responsive_tables_filter($text)) {
      $result = new FilterProcessResult($filtered);
      // Attach Tablesaw library assets to this page.
      $result->setAttachments([
        'library' => ['responsive_tables_filter/tablesaw-filter'],
      ]);
    }
    else {
      $result = new FilterProcessResult($text);
    }

    return $result;
  }

  /**
   * Business logic for adding classes & attributes to <table> tags.
   */
  public function responsive_tables_filter($text) {
    // Older versions of libxml always add DOCTYPE, <html>, and <body> tags.
    // See http://www.php.net/manual/en/libxml.constants.php.
    // Sometimes, PHP is >= 5.4, but libxml is old enough that the constants are
    // not defined.
    static $new_libxml;
    if (!isset($new_libxml)) {
      $new_libxml = version_compare(PHP_VERSION, '5.4', '>=') && defined('LIBXML_HTML_NOIMPLIED') && defined('LIBXML_HTML_NODEFDTD');
    }
    if ($text != '') {
      $tables = [];
      libxml_use_internal_errors(TRUE);
      // LibXML requires that the html is wrapped in a root node.
      $text = '<root>' . $text . '</root>';
      $dom = new \DOMDocument();
      if ($new_libxml) {
        $dom->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
      }
      else {
        $dom->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8'));
      }

      $tables = $dom->getElementsByTagName('table');
      // Find all tables in text.
      if ($tables->length !== 0) {
        foreach ($tables as $table) {
          // Find existing class attributes, if any, and append tablesaw class.
          $existing_classes = $table->getAttribute('class');
          if (strpos($existing_classes, 'no-tablesaw') === FALSE) {
            $new_classes = !empty($existing_classes) ? $existing_classes . ' tablesaw tablesaw-stack' : 'tablesaw tablesaw-stack';
            $table->setAttribute('class', $new_classes);

            // Force data-tablesaw-mode attribute to be "stack".
            $table->setAttribute('data-tablesaw-mode', 'stack');
          }
        }
        // Get innerHTML of root node.
        $html = "";
        foreach ($dom->getElementsByTagName('root')->item(0)->childNodes as $child) {
          // Re-serialize the HTML.
          $html .= $dom->saveHTML($child);
        }
        // For lower older libxml, use preg_replace to clean up DOCTYPE.
        if (!$new_libxml) {
          $html_start = strpos($html, '<html><body>') + 12;
          $html_length = strpos($html, '</body></html>') - $html_start;
          $html = substr($html, $html_start, $html_length);
        }

        return $html;
      }
    }
    return FALSE;
  }

}
