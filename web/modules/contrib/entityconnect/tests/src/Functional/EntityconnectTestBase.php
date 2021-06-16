<?php

namespace Drupal\Tests\entityconnect\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * EntityConnect Test Base.
 *
 * @group entityconnect *
 */
abstract class EntityconnectTestBase extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'entityconnect',
    'field_ui',
    'block',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * User with permission to administer entityconnect.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * User with permission to use entityconnect buttons.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $ecUser;

  /**
   * The node type object to test with.
   *
   * @var \Drupal\node\Entity\NodeType
   */
  protected $testContentType;

  /**
   * The test reference field.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $testRefField;

  /**
   * {@inheritDoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a test content type.
    $this->testContentType = $this->drupalCreateContentType(['type' => 'ec_test', 'name' => 'EC Test']);
    // Place the title block.
    $this->drupalPlaceBlock('page_title_block');

    // Add an entity reference field.
    $this->testRefField = $this->addContentEntityReferenceField();

    // Create users.
    $this->adminUser = $this->drupalCreateUser([
      'administer entityconnect',
      'administer site configuration',
      'administer content types',
      'administer node fields',
    ]);

    $this->drupalCreateRole(['entityconnect add button'], 'ec_add');
    $this->drupalCreateRole(['entityconnect edit button'], 'ec_edit');

    $this->ecUser = $this->drupalCreateUser([
      "create {$this->testContentType->id()} content",
      "edit any {$this->testContentType->id()} content",
    ]);
  }

  /**
   * Add an entity reference field to the target content type.
   *
   * @param string $field_name
   *   Name of the reference field.
   * @param string $field_bundle
   *   Bundle on which to add the reference field.
   * @param array|null $target_bundles
   *   The target bundle(s) of the reference field.
   *
   * @return \Drupal\field\Entity\FieldConfig
   *   The created/existing field config object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function addContentEntityReferenceField($field_name = 'entity_reference', $field_bundle = NULL, $target_bundles = NULL) {
    $field_storage = FieldStorageConfig::loadByName('node', $field_name);
    $field = FieldConfig::loadByName('node', $field_bundle ?? $this->testContentType->id(), $field_name);
    if (empty($field)) {
      if (empty($field_storage)) {
        $field_storage = FieldStorageConfig::create([
          'field_name' => $field_name,
          'entity_type' => 'node',
          'type' => 'entity_reference',
          'cardinality' => 1,
        ]);
        $field_storage->save();
      }
      $field = FieldConfig::create([
        'field_storage' => $field_storage,
        'label' => $field_name,
        'bundle' => $field_bundle ?? $this->testContentType->id(),
        'settings' => ['handler_settings' => ['target_bundles' => $target_bundles ?? [$this->testContentType->id()]]],
      ]);
      $field->save();
      /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
      $form_display = \Drupal::service('entity_display.repository')->getFormDisplay('node', $field_bundle ?? $this->testContentType->id(), 'default');
      $form_display->setComponent($field_name, [
        'type' => 'options_select',
      ])
        ->save();

      // Show on default display and teaser.
      \Drupal::service('entity_display.repository')
        ->getViewDisplay('node', $field_bundle ?? $this->testContentType->id())
        ->setComponent($field_name, [
          'type' => 'entity_reference_label',
        ])
        ->save();
    }

    return $field;
  }

  /**
   * Update the target bundles of the test entity reference field.
   *
   * @param array $target_bundles
   *   The target bundles.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function updateEntityReferenceFieldTargets(array $target_bundles = []) {
    if (!$this->testRefField) {
      $this->testRefField = $this->addContentEntityReferenceField();
    }
    $handler_settings = $this->testRefField->getSetting('handler_settings') ?? [];
    $handler_settings['target_bundles'] = array_merge($handler_settings['target_bundles'], $target_bundles);
    $this->testRefField->setSetting('handler_settings', $handler_settings);
    $this->testRefField->save();
  }

  /**
   * Set the entity connect buttons state.
   *
   * @param bool $add
   *   Whether add button should be on or off.
   * @param bool $edit
   *   Whether edit button should be on or off.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setEcButtons($add = TRUE, $edit = TRUE) {
    $this->testRefField->setThirdPartySetting('entityconnect', 'buttons', [
      'button_add' => !$add,
      'button_edit' => !$edit,
    ]);

    if (empty($this->testRefField->getThirdPartySettings('entityconnect')['icons'])) {
      $this->testRefField->setThirdPartySetting('entityconnect', 'icons', [
        'icon_add' => 0,
        'icon_edit' => 0,
      ]);
    }

    $this->testRefField->save();
  }

}
