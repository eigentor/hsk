<?php

namespace Drupal\Tests\better_formats\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the field config 3rd party settings provided by Better Formats.
 *
 * @group better_formats
 */
class BetterFormatsConfigSchemaTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'better_formats',
    'better_formats_test',
    'field',
    'filter',
    'node',
    'system',
    'text',
    'user',
  ];

  /**
   * Tests the field config 3rd party settings provided by Better Formats.
   */
  public function testConfigSchema() {
    // Check that schema is valid by installing the configuration provided by
    // the 'better_formats_test' module which contains a node body field with
    // Better Formats third party settings.
    $this->installConfig(['node', 'better_formats_test']);
    $this->assertTrue(FieldConfig::load('node.page.body'));
  }

}
