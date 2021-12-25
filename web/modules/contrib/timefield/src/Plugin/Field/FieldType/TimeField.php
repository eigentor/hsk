<?php

namespace Drupal\timefield\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'timefield' field type.
 *
 * @FieldType(
 *   id = "timefield",
 *   label = @Translation("Timefield"),
 *   module = "timefield",
 *   description = @Translation("This field stores a time in the database"),
 *   default_widget = "timefield_standard_widget",
 *   default_formatter = "timefield_default_formatter"
 * )
 */
class TimeField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'totime' => '',
      'weekly_summary' => NULL,
      'weekly_summary_with_label' => NULL,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {

    $element = [];

    $element['totime'] = [
      '#title' => $this->t('To Time'),
      '#type' => 'select',
      '#options' => [
        '' => $this->t('Never'),
        'optional' => $this->t('Optional'),
        'required' => $this->t('Required'),
      ],
      '#description' => $this->t('Whether this field should include an end time.'),
      '#default_value' => $this->getSetting('totime'),
    ];
    $element['weekly_summary'] = [
      '#title' => $this->t('Add Weekly Repeat Checkboxes'),
      '#type' => 'checkbox',
      '#description' => $this->t('Should this field include options to specify the days on which it repeats.'),
      '#default_value' => $this->getSetting('weekly_summary'),
    ];
    $element['weekly_summary_with_label'] = [
      '#title' => $this->t('Add Weekly Repeat Checkboxes with Label for each Time'),
      '#type' => 'checkbox',
      '#description' => $this->t('Same as above with an additional label field for describing times.'),
      '#default_value' => $this->getSetting('weekly_summary_with_label'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type'     => 'int',
          'not null' => FALSE,
          'default'  => NULL,
        ],
        'value2' => [
          'type'     => 'int',
          'not null' => FALSE,
          'default'  => NULL,
        ],
        'label' => [
          'description' => 'A label for this weekly schedule',
          'type'     => 'varchar',
          'length' => 255,
          'not null' => FALSE,
          'default'  => '',
        ],
        'mon' => [
          'type'     => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ],
        'tue' => [
          'type'     => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ],
        'wed' => [
          'type'     => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ],
        'thu' => [
          'type'     => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ],
        'fri' => [
          'type'     => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ],
        'sat' => [
          'type'     => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ],
        'sun' => [
          'type'     => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(t('Timefield'));
    $properties['value2'] = DataDefinition::create('integer')
      ->setLabel(t('Timefield2'));
    $properties['label'] = DataDefinition::create('string')
      ->setLabel(t('Label'));
    $properties['mon'] = DataDefinition::create('integer')
      ->setLabel(t('Monday'));
    $properties['tue'] = DataDefinition::create('integer')
      ->setLabel(t('Tuesday'));
    $properties['wed'] = DataDefinition::create('integer')
      ->setLabel(t('Wednesday'));
    $properties['thu'] = DataDefinition::create('integer')
      ->setLabel(t('Thursday'));
    $properties['fri'] = DataDefinition::create('integer')
      ->setLabel(t('Friday'));
    $properties['sat'] = DataDefinition::create('integer')
      ->setLabel(t('Saturday'));
    $properties['sun'] = DataDefinition::create('integer')
      ->setLabel(t('Sunday'));
    return $properties;
  }

}
