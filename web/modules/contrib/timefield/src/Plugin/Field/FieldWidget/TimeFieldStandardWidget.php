<?php

namespace Drupal\timefield\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Asset\LibraryDiscovery;

/**
 * Plugin implementation of the 'timefield_standard_widget' widget.
 *
 * @FieldWidget(
 *  id = "timefield_standard_widget",
 *  label = @Translation("Timefield"),
 *  field_types = {"timefield"}
 * )
 */
class TimeFieldStandardWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Field widget display plugin manager.
   *
   * @var \Drupal\entity_browser\FieldWidgetDisplayManager
   */
  protected $fieldDisplayManager;

  /**
   * The depth of the delete button.
   *
   * This property exists so it can be changed if subclasses.
   *
   * @var int
   */
  protected static $deleteDepth = 4;

  /**
   * The module handler interface.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * If triggering element was hidden target_id element.
   *
   * @var bool
   */
  protected $entityBrowserValueUpdated;

  /**
   * If triggering element was hidden target_id element.
   *
   * @var \Drupal\Core\Asset\LibraryDiscovery
   */
  protected $libraryDiscovery;

  /**
   * Constructs widget plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Asset\LibraryDiscovery $libraryDiscovery
   *   The library discovery.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface|null $module_handler
   *   The module handler.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, LibraryDiscovery $libraryDiscovery, ModuleHandlerInterface $module_handler) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->libraryDiscovery = $libraryDiscovery;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('library.discovery'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $library = \Drupal::service('library.discovery')->getLibraryByName('timefield', 'timepicker');
    return [
      'disable_plugin' => empty($library) ? TRUE : FALSE,
      'input_format' => [
        'separator' => ':',
        'showLeadingZero' => FALSE,
        'showMinutesLeadingZero' => TRUE,
        'showPeriod' => TRUE,
        'periodSeparator' => '',
        'showHours' => TRUE,
        'showMinutes' => TRUE,
        'am_text' => 'AM',
        'pm_text' => 'PM',
        'minute_interval' => 5,
        'showCloseButton' => FALSE,
        'closeButtonText' => 'Done',
        'showNowButton' => FALSE,
        'nowButtonText' => 'Now',
        'showDeselectButton' => FALSE,
        'deselectButtonText' => 'Deselect',
        'myPosition' => 'left top',
        'atPosition' => 'left bottom',
      ],

    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $library = $this->libraryDiscovery->getLibraryByName('timefield', 'timepicker');
    if (empty($library)) {
      $this->messenger()->addWarning($this->t("You will not have enhanced time input widget without downloading the plugin. %link", ['%link' => Link::fromTextAndUrl("Read installation instructions here.", Url::fromUserInput('http://drupalcode.org/project/timefield.git/blob_plain/HEAD:/README.txt'))]));
    }
    $elements['disable_plugin'] = [
      '#title' => $this->t('Disable jQuery Timepicker plugin.'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('disable_plugin'),
      '#description' => $this->t('Do not use jQuery Timepicker plugin for input.'),
      '#disabled' => (empty($library)),
      '#attributes' => [
        'name' => 'disable_plugin',
      ],
    ];

    $elements['separator'] = [
      '#title' => $this->t('Hour and Minute Separator'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('separator'),
      '#size' => 10,
      '#description' => $this->t('The character to use to separate hours and minutes.'),
    ];
    $elements['showLeadingZero'] = [
      '#title' => $this->t('Show Leading Zero for Hour'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('showLeadingZero'),
      '#description' => $this->t('Whether or not to show a leading zero for hours < 10.'),
    ];
    $elements['showPeriod'] = [
      '#title' => $this->t('Show AM/PM Label'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('showPeriod'),
      '#description' => $this->t('Whether or not to show AM/PM on the input textfield both on the widget and in the text field after selecting the time with the widget.'),
    ];
    $elements['periodSeparator'] = [
      '#title' => $this->t('What character should appear between the time and the Period (AM/PM)'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('periodSeparator'),
      '#size' => 10,
      '#description' => $this->t('The character to use to separate the time from the time period (AM/PM).'),
    ];
    $elements['am_text'] = [
      '#title' => $this->t('AM text'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('am_text'),
      '#size' => 10,
    ];
    $elements['pm_text'] = [
      '#title' => $this->t('PM text'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('pm_text'),
      '#size' => 10,
    ];
    $elements['showCloseButton'] = [
      '#title' => $this->t('Show a Button to Close the Picker Widget'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('showCloseButton'),
      '#states' => [
        'invisible' => [
          ':input[name=disable_plugin]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['closeButtonText'] = [
      '#title' => $this->t('Close Button text'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('closeButtonText'),
      '#size' => 10,
      '#states' => [
        'invisible' => [
          ':input[name=disable_plugin]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['showNowButton'] = [
      '#title' => $this->t('Show a Button to Select the Current Time'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('showNowButton'),
      '#states' => [
        'invisible' => [
          ':input[name=disable_plugin]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['nowButtonText'] = [
      '#title' => $this->t('Now Button text'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('nowButtonText'),
      '#size' => 10,
      '#states' => [
        'invisible' => [
          ':input[name=disable_plugin]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['showDeselectButton'] = [
      '#title' => $this->t('Show a Button to Deselect the time in the Picker Widget'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('showDeselectButton'),
      '#states' => [
        'invisible' => [
          ':input[name=disable_plugin]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['deselectButtonText'] = [
      '#title' => $this->t('Deselect Button text'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('deselectButtonText'),
      '#size' => 10,
      '#states' => [
        'invisible' => [
          ':input[name=disable_plugin]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['myPosition'] = [
      '#title' => $this->t('my Position'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('myPosition'),
      '#options' => array_combine(
          [
            'left top',
            'left center',
            'left bottom',
            'center top',
            'center center',
            'center bottom',
            'right top',
            'right center',
            'right bottom',
          ], [
            'left top',
            'left center',
            'left bottom',
            'center top',
            'center center',
            'center bottom',
            'right top',
            'right center',
            'right bottom',
          ]
      ),
      '#description' => $this->t('Corner of the timpicker widget dialog to position. See !jquery_info for more info.', ['!jquery_info' => Link::fromTextAndUrl($this->t("jQuery UI Position documentation"), Url::fromUri('http://jqueryui.com/demos/position'))]),
      '#states' => [
        'invisible' => [
          ':input[name=disable_plugin]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['atPosition'] = [
      '#title' => $this->t('at Position'),
      '#type' => 'select',
      '#options' => array_combine(
          [
            'left top',
            'left center',
            'left bottom',
            'center top',
            'center center',
            'center bottom',
            'right top',
            'right center',
            'right bottom',
          ], [
            'left top',
            'left center',
            'left bottom',
            'center top',
            'center center',
            'center bottom',
            'right top',
            'right center',
            'right bottom',
          ]
      ),
      '#default_value' => $this->getSetting('atPosition'),
      '#description' => $this->t('Where to position "my Position" relative to input widget textfield See !jquery_info for more info.', ['!jquery_info' => Link::fromTextAndUrl($this->t("jQuery UI Position documentation"), Url::fromUri('http://jqueryui.com/demos/position'))]),
      '#states' => [
        'invisible' => [
          ':input[name=disable_plugin]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary[] = $this->t('Number of summary rows: @rows', ['@rows' => $this->getSetting('summary_rows')]);
    if ($this->getSetting('show_summary')) {
      $summary[] = $this->t('Summary field will always be visible');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $wrapper = FALSE;
    $instance_class = str_replace('_', '-', $items->getName()) . "-" . $delta;
    $instance_settings = $this->getSettings();
    $field_settings = $this->getFieldSettings();
    if (!$instance_settings['disable_plugin']) {
      $js_settings = _timefield_js_settings($instance_class, $instance_settings);
      $context = [
        'type' => 'field',
        'items' => $items,
      ];

      $this->moduleHandler->alter('timefield_js_settings', $js_settings, $context);

      $element['#attached']['library'][] = 'timefield/timepicker';
      $element['#attached']['library'][] = 'timefield/timefield';
      $element['#attached']['drupalSettings']['timefield'][$instance_class] = $js_settings;
    }

    $element += [
      '#delta' => $delta,
    ];

    if ($field_settings['weekly_summary_with_label']) {
      $wrapper = TRUE;
      $element['label'] = [
        '#title' => $this->t('Label'),
        '#description' => $this->t('Enter a label for the summary'),
        '#type' => 'textfield',
        '#default_value' => isset($items[$delta]->label) ? $items[$delta]->label : '',
        '#size' => 40,
        '#maxlength' => 60,
      ];
    }

    $value = isset($items[$delta]) ? timefield_integer_to_time($instance_settings, $items[$delta]->value) : '';
    $element['value'] = [
      '#type' => 'textfield',
      '#title' => Xss::filter($element['#title']),
      '#description' => Xss::filter($element['#description']),
      '#default_value' => $value,
      '#required' => $element['#required'],
      '#weight' => (isset($element['#weight'])) ? $element['#weight'] : 0,
      '#delta' => $delta,
      '#element_validate' => [[static::class, 'validateTimeField']],
      '#attributes' => [
        'class' => [
          'edit-timefield-timepicker',
          $instance_class,
        ],
      ],
    ];

    if ($field_settings['totime'] == 'required' || $field_settings['totime'] == 'optional') {
      $wrapper = TRUE;
      $value2 = isset($items[$delta]) ? timefield_integer_to_time($instance_settings, $items[$delta]->value2) : '';
      $element['value2'] = [
        '#type' => 'textfield',
        '#title' => $this->t('End Time'),
        '#description' => $this->t('Enter a time value, in any format'),
        '#default_value' => $value2,
        '#required' => $element['#required'],
        '#weight' => (isset($element['#weight'])) ? $element['#weight'] : 0,
        '#delta' => $delta,
        '#element_validate' => [[static::class, 'validateTimeField']],
        '#attributes' => [
          'class' => [
            'edit-timefield-timepicker',
            $instance_class,
          ],
        ],
      ];
    }

    if ($field_settings['weekly_summary'] || $field_settings['weekly_summary_with_label']) {
      $wrapper = TRUE;
      $days = isset($items[$delta]->mon) ? _timefield_weekly_summary_days_map($items[$delta]) : [];
      $weekDays = _timefield_weekly_summary_days();
      $element['days'] = [
        '#type' => 'container',
        '#description' => $this->t('Select the days this schedule applies to'),
        '#attributes' => [
          'class' => [
            'edit-field-timefield-days',
          ],
        ],
      ];

      foreach ($weekDays as $key => $day) {
        $element[$key] = [
          '#title' => $day,
          '#type' => 'checkbox',
          '#default_value' => (!empty($days) && ($days[$key] != '0')) ? 1 : 0,
          '#attributes' => ['class' => ['edit-field-timefield-days']],
        ];
        if ($key == 'mon') {
          $element[$key]['#prefix'] = new FormattableMarkup('<b>@label</b>', ['@label' => 'Days']);
          ;
        }
        elseif ($key == 'sun') {
          $element[$key]['#suffix'] = $this->t('Select the days this schedule applies to');
        }
      }
    }

    if ($wrapper) {
      $element['#theme_wrappers'][] = 'fieldset';
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * Validation Callback; Timefield process field.
   */
  public static function validateTimeField($element, FormStateInterface $form_state) {
    // If empty, set to null.
    if (strlen($element['#value']) == 0) {
      if (!empty($element['#required'])) {
        $form_state->setError($element, new TranslatableMarkup('@name field is required.', ['@name' => Html::escape($element['#title'])]));
      }
      $form_state->setValueForElement($element, NULL);
      return;
    }
    $date_value = date_parse($element['#value']);
    if ($date_value['error_count']) {
      $form_state->setError($element, new TranslatableMarkup('Please enter the time in a valid format'));
    }
    else {
      $parsed_value = timefield_time_to_integer($element['#value']);
      $form_state->setValueForElement($element, $parsed_value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    $element = parent::errorElement($element, $violation, $form, $form_state);
    return ($element === FALSE) ? FALSE : $element[$violation->arrayPropertyPath[0]];
  }

}
