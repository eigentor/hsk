<?php

namespace Drupal\hms_field\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a one-line text field form element.
 *
 * @FormElement("hms")
 */
class HMS extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {

    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#size' => 8,
      '#maxlength' => 16,
      '#default_value' => FALSE,
      '#format' => 'h:mm:ss',
      '#placeholder' => 'h:mm:ss',
      '#autocomplete_route_name' => FALSE,
      '#process' => [
        [$class, 'processAutocomplete'],
        [$class, 'processAjaxForm'],
        [$class, 'processPattern'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderHms'],
      ],
      '#element_validate' => [
        [$class, 'validateHms'],
      ],
      '#theme' => 'input__textfield',
      '#theme_wrappers' => ['form_element'],

    ];
  }

  /**
   * Form element validation handler for #type 'hms'.
   *
   * Note that #required is validated by _form_validate() already.
   */
  public static function validateHms(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);

    $form_state->setValueForElement($element, $value);
    if ($value !== '' && !\Drupal::service('hms_field.hms')->isValid($value, $element['#format'], $element, $form_state)) {
      $form_state->setError($element, t('Please enter a correct hms value in format %format.', ['%format' => $element['#format']]));
    }
    else {
      // Format given value to seconds if input is valid.
      $seconds = \Drupal::service('hms_field.hms')->formattedToSeconds($value, $element['#format'], $element, $form_state);
      $form_state->setValueForElement($element, $seconds);
    }
  }

  /**
   * Prepares a #type 'hms' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderHms(array $element) {

    Element::setAttributes($element, [
      'id',
      'name',
      'value',
      'size',
      'maxlength',
      'placeholder',
    ]);
    static::setAttributes($element, ['form-hms']);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // Get saved value from db.
    if ($input === FALSE) {
      $formatted = \Drupal::service('hms_field.hms')->secondsToFormatted($element['#default_value'], $element['#format']);
      return $formatted;
    }
  }

}
