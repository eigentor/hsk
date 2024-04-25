<?php

namespace Drupal\Tests\time_field\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the time field.
 *
 * @group time_field
 */
class TimeFieldTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'time_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The content type to be used in this test.
   *
   * @var string
   */
  protected $contentType = 'test_content';

  /**
   * The field name to be used in this test.
   *
   * @var string
   */
  protected $fieldName = 'field_test_time';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalCreateContentType([
      'type' => $this->contentType,
      'name' => 'Test content',
    ]);

    // Add a duration field to test content type.
    $fieldStorage = FieldStorageConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => 'node',
      'type' => 'time',
      'settings' => [],
    ]);
    $fieldStorage->save();
    $field = FieldConfig::create([
      'field_storage' => $fieldStorage,
      'bundle' => $this->contentType,
      'required' => TRUE,
    ]);
    $field->save();

    // Configure the widget and formatter to make sure field is shown.
    $form = \Drupal::configFactory()
      ->getEditable('core.entity_form_display.node.' . $this->contentType . '.default');
    $form->set('content.' . $this->fieldName . '.type', 'time_widget')
      ->set('content.' . $this->fieldName . '.settings', [
        'enabled' => FALSE,
        'step' => 5,
      ])
      ->set('content.' . $this->fieldName . '.third_party_settings', [])
      ->set('content.' . $this->fieldName . '.weight', 0)
      ->save();
    $form = \Drupal::configFactory()
      ->getEditable('core.entity_view_display.node.' . $this->contentType . '.default');
    $form->set('content.' . $this->fieldName . '.type', 'time_formatter')
      ->set('content.' . $this->fieldName . '.settings', [
        'time_format' => 'h:i a',
      ])
      ->set('content.' . $this->fieldName . '.third_party_settings', [])
      ->set('content.' . $this->fieldName . '.weight', 0)
      ->set('content.' . $this->fieldName . '.label', 'hidden')
      ->save();

    // Create test user for creating test nodes.
    $this->drupalLogin($this->drupalCreateUser([
      'create ' . $this->contentType . ' content',
    ]));
  }

  /**
   * Tests the time field (required).
   *
   * @dataProvider timeFieldRequiredDataProvider
   */
  public function testTimeFieldRequired($time, $expected = NULL, $error = NULL) {
    // Try to create a test node with the given time value.
    $this->drupalGet('node/add/' . $this->contentType);
    $this->assertSession()->statusCodeEquals(200);
    $this->submitForm([
      'title[0][value]' => 'Test node',
      $this->fieldName . '[0][value]' => $time,
    ], 'Save');
    $this->assertSession()->statusCodeEquals(200);

    // If errror is expected, check for the error message.
    if ($error) {
      $this->assertSession()->pageTextContains($error);
    }
    // Otherwise, check for the expected value.
    else {
      $this->assertSession()->addressMatches('/^\/node\/\d$/');
      $this->assertSession()->pageTextContains($expected);
    }
  }

  /**
   * Tests the time field.
   *
   * @dataProvider timeFieldDataProvider
   */
  public function testTimeField($time, $expected = NULL, $error = NULL) {
    // Set the field to not required.
    $field = FieldConfig::loadByName('node', $this->contentType, $this->fieldName);
    $field->set('required', FALSE);
    $field->save();

    // Try to create a test node with the given time value.
    $this->drupalGet('node/add/' . $this->contentType);
    $this->assertSession()->statusCodeEquals(200);
    $this->submitForm([
      'title[0][value]' => 'Test node',
      $this->fieldName . '[0][value]' => $time,
    ], 'Save');
    $this->assertSession()->statusCodeEquals(200);

    // If errror is expected, check for the error message.
    if ($error) {
      $this->assertSession()->pageTextContains($error);
    }
    // Otherwise, check for the expected value.
    else {
      $this->assertSession()->addressMatches('/^\/node\/\d$/');
      $this->assertSession()->pageTextContains($expected);
    }
  }

  /**
   * Data provider for testTimeFieldRequired().
   *
   * @return array
   *   An array of test data.
   */
  public function timeFieldRequiredDataProvider() {
    return [
      // Correct time values.
      ['00:00', '12:00 am'],
      ['03:30', '03:30 am'],
      ['12:00', '12:00 pm'],
      ['23:59', '11:59 pm'],
      // Empty value is only allowed when not required.
      [NULL, NULL, 'This value should not be null.'],
    ];
  }

  /**
   * Data provider for testTimeField().
   *
   * @return array
   *   An array of test data.
   */
  public function timeFieldDataProvider() {
    return [
      // Correct time values.
      ['00:00', '12:00 am'],
      ['03:30', '03:30 am'],
      ['12:00', '12:00 pm'],
      ['23:59', '11:59 pm'],
      // Empty value is only allowed when not required.
      ['', ''],
    ];
  }

}
