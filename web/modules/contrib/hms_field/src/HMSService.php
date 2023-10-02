<?php

namespace Drupal\hms_field;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a service to handle various hms related functionality.
 *
 * @package Drupal\hms_field
 */
class HMSService implements HMSServiceInterface {

  use StringTranslationTrait;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * Constructs HMSService.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler class.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function formatOptions(): array {
    $format = drupal_static(__FUNCTION__);
    if (empty($format)) {
      $format = [
        'ISO 8601 based' => [
          'h:mm' => 'h:mm',
          'hh:mm:ss' => 'hh:mm:ss',
          'h:mm:ss' => 'h:mm:ss',
          'm:ss' => 'm:ss',
          'h' => 'h',
          'm' => 'm',
          's' => 's',
        ],
        'Space separated' => [
          'hms' => 'e.q. 3h 15m 30s',
        ],
      ];
      $this->moduleHandler->alter('hms_format', $format);
    }
    return $format;
  }

  /**
   * {@inheritdoc}
   */
  public function factorMap(bool $return_full = FALSE): array {
    $factor = drupal_static(__FUNCTION__);
    if (empty($factor)) {
      $factor = [
        'w' => [
          'factor value' => 604800,
          'label single' => $this->t('week'),
          'label multiple' => $this->t('weeks'),
        ],
        'd' => [
          'factor value' => 86400,
          'label single' => $this->t('day'),
          'label multiple' => $this->t('days'),
        ],
        'h' => [
          'factor value' => 3600,
          'label single' => $this->t('hour'),
          'label multiple' => $this->t('hours'),
        ],
        'm' => [
          'factor value' => 60,
          'label single' => $this->t('minute'),
          'label multiple' => $this->t('minutes'),
        ],
        's' => [
          'factor value' => 1,
          'label single' => $this->t('second'),
          'label multiple' => $this->t('seconds'),
        ],
      ];
      $this->moduleHandler->alter('hms_factor', $factor);
    }

    if ($return_full) {
      return $factor;
    }

    // We only return the factor value here.
    // for historical reasons we also check if value is an array.
    $return = [];
    foreach ($factor as $key => $val) {
      $value = (is_array($val) ? $val['factor value'] : $val);
      $return[$key] = $value;
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function formattedToSeconds(string $str, string $format = 'h:m:s', array $element = [], FormStateInterface $form_state = NULL): int|bool|NULL {
    if (!strlen($str)) {
      return NULL;
    }
    elseif ($str == '0') {
      return 0;
    }
    $value = 0;
    $error = FALSE;

    // Input validation for space separated format.
    if ($format == 'hms') {
      $preg = [];
      if ((is_numeric($str) || preg_match('/^(?P<H>[-]{0,1}[0-9]{1,5}(\.[0-9]{1,3})?)$|^(?P<negative>[-]{0,1})(((?P<w>[0-9.]{1,5})w)?((?P<d>[0-9.]{1,5})d)?((?P<h>[0-9.]{1,5})h)?([ ]{0,1})((?P<m>[0-9.]{1,05})m)?([ ]{0,1})((?P<s>[0-9.]{1,5})s)?)/', $str, $preg))) {
        $error = TRUE;
        foreach ($preg as $code => $val) {
          if (!is_numeric($val)) {
            continue;
          }
          switch ($code) {
            case 'w':
              $error = FALSE;
              $value += $val * 604800;
              break;

            case 'd':
              $error = FALSE;
              $value += $val * 86400;
              break;

            case 'h':
            case 'H':
              $error = FALSE;
              $value += $val * 3600;
              break;

            case 'm':
              $error = FALSE;
              $value += $val * 60;
              break;

            case 's':
              $error = FALSE;
              $value += $val;
              break;

            default:
              break;
          }
        }
        if (!empty($preg['negative'])) {
          $value = $value * -1;
        }
        if ($error == 0) {
          return (int) $value;
        }
      }
      else {
        $error = TRUE;
      }
    }

    // Input validation ISO 8601 based.
    $preg_string = preg_replace(
      ['/[h]{1,6}/', '/[m]{1,2}|[s]{1,2}/'],
      ['([0-9]{1,6})', '([0-9]{1,2})'],
      $format
    );
    if (!preg_match("@^" . $preg_string . "$@", $str) && !preg_match('/^[0-9]{1,6}([,.][0-9]{1,6})?$/', $str)) {
      $error = TRUE;
    }

    // Does not follow space separated format.
    if ($error) {
      if (!empty($form_state)) {
        $form_state->setErrorByName('field_name', $this->t('The %name value is in wrong format, check in field settings.', ['%name' => $element['#title']]));
      }
      return FALSE;
    }

    // Is the value negative?
    $negative = FALSE;
    if (substr($str, 0, 1) == '-') {
      $negative = TRUE;
      $str = substr($str, 1);
    }

    $factor_map = $this->factorMap();
    $search = $this->normalizeFormat($format);

    for ($i = 0; $i < strlen($search); $i++) {
      // Is this char in the factor map?
      if (isset($factor_map[$search[$i]])) {
        $factor = $factor_map[$search[$i]];
        // What is the next seperator to search for?
        $bumper = '$';
        if (isset($search[$i + 1])) {
          $bumper = '(' . preg_quote($search[$i + 1], '/') . '|$)';
        }
        if (preg_match_all('/^(.*)' . $bumper . '/U', $str, $matches)) {
          // Replace , with .
          $num = str_replace(',', '.', $matches[1][0]);
          // Return error when found string is not numeric.
          if (!is_numeric($num)) {
            return FALSE;
          }
          // Shorten $str.
          $str = substr($str, strlen($matches[1][0]));
          // Calculate value.
          $value += ($num * $factor);
        }

      }
      elseif (substr($str, 0, 1) == $search[$i]) {
        // Expected this value, cut off and go ahead.
        $str = substr($str, 1);
      }
      else {
        // Does not follow format.
        return FALSE;
      }
      if (!strlen($str)) {
        // No more $str to investigate.
        break;
      }
    }

    if ($negative) {
      $value = 0 - $value;
    }
    return (int) $value;
  }

  /**
   * {@inheritdoc}
   */
  public function secondsToFormatted(string|int $seconds, string $format = 'h:mm', bool $leading_zero = TRUE): ?string {
    // Return NULL on empty string.
    if ($seconds === '' || is_null($seconds)) {
      return NULL;
    }

    $factor = $this->factorMap();
    // We need factors, biggest first.
    arsort($factor, SORT_NUMERIC);
    $values = [];
    $left_over = $seconds;
    $str = '';

    if ($seconds < 0) {
      $str .= '-';
      $left_over = abs($left_over);
    }

    // Space separated format.
    if ($format == 'hms') {
      foreach ($factor as $key => $val) {
        if ($left_over == 0) {
          break;
        }
        $values[$key] = floor($left_over / $factor[$key]);
        if ($values[$key]) {
          $left_over -= ($values[$key] * $factor[$key]);
          $str .= $values[$key] . $key . ' ';
        }
      }
    }

    // ISO based formats.
    else {
      foreach ($factor as $key => $val) {
        if (strpos($format, $key) === FALSE) {
          // Not in our format, please go on, so we can plus this on a value in
          // our format.
          continue;
        }
        if ($left_over == 0) {
          $values[$key] = 0;
          continue;
        }
        $values[$key] = floor($left_over / $factor[$key]);
        $left_over -= ($values[$key] * $factor[$key]);
      }
      $format = explode(':', $format);
      foreach ($format as $key) {
        if (!$leading_zero && (empty($values[substr($key, 0, 1)]) || !$values[substr($key, 0, 1)])) {
          continue;
        }
        $leading_zero = TRUE;
        $str .= sprintf('%0' . strlen($key) . 'd', $values[substr($key, 0, 1)]) . ':';
      }
      if (!strlen($str)) {
        $key = array_pop($format);
        $str = sprintf('%0' . strlen($key) . 'd', 0) . ':';
      }
    }

    return substr($str, 0, -1);
  }

  /**
   * {@inheritdoc}
   */
  public function isValid(string|int $input, string $format, array $element = [], FormStateInterface $form_state = NULL): bool {
    if ($this->formattedToSeconds($input, $format, $element, $format_state) !== FALSE) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalizeFormat(string $format): string {
    $keys = array_keys($this->factorMap());
    $search_keys = array_map([$this, 'addMultiSearchTokens'], $keys);
    return preg_replace($search_keys, $keys, $format);
  }

  /**
   * {@inheritdoc}
   */
  public function addMultiSearchTokens(string $item): string {
    return '/' . $item . '+/';
  }

}
