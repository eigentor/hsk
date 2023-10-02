<?php

namespace Drupal\hms_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'hms' field type.
 *
 * @FieldType(
 *   id = "hms",
 *   label = @Translation("Hours Minutes and Seconds"),
 *   description = @Translation("Store Hours, Minutes or Seconds as an integer."),
 *   default_widget = "hms_default",
 *   default_formatter = "hms_default_formatter"
 * )
 */
class HMSFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('HMS integer value'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'value' => [
          'type' => 'int',
          'unsigned' => FALSE,
          'not null' => FALSE,
        ],
      ],
    ];
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

}
