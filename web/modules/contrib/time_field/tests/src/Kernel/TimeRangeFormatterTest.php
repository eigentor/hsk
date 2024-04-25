<?php

namespace Drupal\Tests\time_field\Kernel;

use Drupal\Core\Field\FieldItemList;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\time_field\Time;

/**
 * Tests the time range formatters functionality.
 *
 * @group custom
 */
class TimeRangeFormatterTest extends EntityKernelTestBase {

  /**
   * The entity type used in this test.
   *
   * @var string
   */
  protected $entityType = 'entity_test';

  /**
   * The bundle used in this test.
   *
   * @var string
   */
  protected $bundle = 'entity_test';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['datetime', 'language', 'time_field'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    FieldStorageConfig::create([
      'field_name' => 'field_under_test',
      'entity_type' => $this->entityType,
      'type' => 'time_range',
      'settings' => [],
    ])->save();

    FieldConfig::create([
      'entity_type' => $this->entityType,
      'bundle' => $this->bundle,
      'field_name' => 'field_under_test',
      'label' => 'Field label',
      'required' => TRUE,
    ])->save();
  }

  /**
   * Create the entity to be referenced.
   *
   * @param array $field_data
   *   Field data for the field we are testing.
   *
   * @return \Drupal\Core\Field\FieldItemList
   *   The field.
   */
  protected function createFieldData(array $field_data): FieldItemList {
    $entity = $this->container->get('entity_type.manager')
      ->getStorage($this->entityType)
      ->create(['name' => $this->randomMachineName()]);
    $entity->field_under_test = $field_data;
    $entity->save();

    return $entity->get('field_under_test');
  }

  /**
   * Tests the entity output when saving without a to value.
   */
  public function testWithEmptyTo() {
    // 49230.
    $time = new Time(13, 40, 30);

    // Verify the entity was save with an empty to.
    $result = $this->createFieldData(['from' => $time->getTimestamp(), 'to' => '']);
    $this->assertEmpty($result->to);
    $this->assertEquals("49230", $result->from);
  }

  /**
   * Tests the entity output when saving without a from value.
   */
  public function testWithEmptyFrom() {
    // 49230.
    $time = new Time(13, 40, 30);

    // Verify the entity was save with an empty to.
    $result = $this->createFieldData(['from' => '', 'to' => $time->getTimestamp()]);
    $this->assertEmpty($result->from);
    $this->assertEquals("49230", $result->to);
  }

  /**
   * Tests the entity output when saving without a value (86401).
   */
  public function testWithBothEmpty() {
    // Verify the entity was save with an empty to.
    $result = $this->createFieldData(['from' => '86401', 'to' => '86401']);
    // NULL means it's not saved.
    $this->assertNull($result->from);
    $this->assertNull($result->to);
  }

  /**
   * Tests the entity output when saving without a value.
   */
  public function testWithBothEmptyStrings() {
    // Verify the entity was save with an empty to.
    $result = $this->createFieldData(['from' => '', 'to' => '']);
    // NULL means it's not saved.
    $this->assertNull($result->from);
    $this->assertNull($result->to);
  }

}
