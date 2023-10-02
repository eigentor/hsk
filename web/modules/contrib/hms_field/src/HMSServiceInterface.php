<?php

namespace Drupal\hms_field;

use Drupal\Core\Form\FormStateInterface;

/**
 * Interface for HMS service.
 */
interface HMSServiceInterface {

  /**
   * Returns possible format options.
   *
   * @return array
   *   List of possible format options.
   */
  public function formatOptions(): array;

  /**
   * Returns the factor map of the format options.
   *
   * Note: We cannot go further then weeks in this setup.
   *       A month implies that we know how many seconds a month is.
   *       Problem here is that a month can be 28(29), 30 or 31 days.
   *       Same goes for C (century) Y (year) Q (quarter).
   *       Only solution is that we have a value relative to a date.
   *
   *  Use HOOK_hms_factor_alter($factors) to do your own magic.
   *
   * @param bool $return_full
   *   Whether to return full value or only the factor value.
   *
   * @return array
   *   The factor map.
   */
  public function factorMap(bool $return_full = FALSE): array;

  /**
   * Returns number of seconds from a formatted string.
   *
   * @param string $str
   *   The formatted string.
   * @param string $format
   *   The format for the string.
   * @param array $element
   *   The element array.
   * @param \Drupal\Core\Form\FormStateInterface|null $form_state
   *   The current form state.
   *
   * @return int|bool|null
   *   The number of seconds, or FALSE in case of error.
   */
  public function formattedToSeconds(string $str, string $format = 'h:m:s', array $element = [], FormStateInterface $form_state = NULL): int|bool|NULL;

  /**
   * Returns a formatted string form the number of seconds.
   *
   * @param string|int $seconds
   *   The number of seconds.
   * @param string $format
   *   The format for the string.
   * @param bool $leading_zero
   *   Whether to show leading zero.
   *
   * @return string|null
   *   The formatted string.
   */
  public function secondsToFormatted(string|int $seconds, string $format = 'h:mm', bool $leading_zero = TRUE): ?string;

  /**
   * Validate hms field input.
   *
   * @param string|int $input
   *   The field input.
   * @param string $format
   *   The format.
   * @param array $element
   *   The element array.
   * @param \Drupal\Core\Form\FormStateInterface|null $form_state
   *   The current form state.
   *
   * @return bool
   *   Whether this is valid or not.
   */
  public function isValid(string|int $input, string $format, array $element = [], FormStateInterface $form_state = NULL): bool;

  /**
   * Helper to normalize format.
   *
   * Changes double keys to single keys.
   *
   * @param string $format
   *   The format to normalize.
   *
   * @return string
   *   The normalized format.
   */
  public function normalizeFormat(string $format): string;

  /**
   * Helper to extend values in search array.
   *
   * @param string $item
   *   Item to process.
   *
   * @return string
   *   The processed item.
   */
  public function addMultiSearchTokens(string $item): string;

}
