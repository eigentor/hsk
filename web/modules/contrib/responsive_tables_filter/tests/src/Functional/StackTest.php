<?php

/**
 * @file
 * Contains \Drupal\responsive_tables_filter\Tests\FilterTest.
 */

namespace Drupal\Tests\responsive_tables_filter\Functional;

/**
 * Tests the responsive_tables_filter filter.
 *
 * @group responsive_tables_filter
 */
class StackTest extends TestBase {

  /**
   * {@inheritdoc}
   */
  protected $mode = 'stack';

  /**
   * Input & output for stack mode.
   *
   * @var data
   */
  private static $data = [
    '<table class="no-tablesaw"></table>' => '<table class="no-tablesaw"></table>',
    '<table></table>' => '<table class="tablesaw tablesaw-stack" data-tablesaw-mode="stack" data-tablesaw-minimap=""></table>',
    '<table class="test"></table>' => '<table class="test tablesaw tablesaw-stack" data-tablesaw-mode="stack" data-tablesaw-minimap=""></table>',
    '<table additional="test"><thead><tr><th data-tablesaw-priority="persist">Header One<th>Header 2<tbody><tr><td>Easily add tables with the WYSIWYG toolbar<td>Encoded characters test öô & , ?<tr><td>Tables respond to display on smaller screens<td>Fully accessible to screen readers</table>' => '<table additional="test" class="tablesaw tablesaw-stack" data-tablesaw-mode="stack" data-tablesaw-minimap=""><thead><tr><th data-tablesaw-priority="persist">Header One</th><th>Header 2</th></tr></thead><tbody><tr><td>Easily add tables with the WYSIWYG toolbar</td><td>Encoded characters test öô &amp; , ?</td></tr><tr><td>Tables respond to display on smaller screens</td><td>Fully accessible to screen readers</td></tr></tbody></table>',
  ];

  /**
   * Tests the responsive_tables_filter Stack (default) mode.
   */
  public function testStack() {
    foreach (self::$data as $input => $output) {
      $settings = [];
      $settings['type'] = 'page';
      $settings['title'] = 'Test Tablesaw Stack Only mode';
      $settings['body'] = [
        'value' => $input,
        'format' => 'custom_format',
      ];
      $node = $this->drupalCreateNode($settings);
      $this->drupalGet('node/' . $node->id());
      $this->assertRaw($output);
    }
  }

}
