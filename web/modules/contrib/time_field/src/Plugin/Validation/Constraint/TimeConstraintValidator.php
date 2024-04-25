<?php

namespace Drupal\time_field\Plugin\Validation\Constraint;

use Drupal\time_field\Time;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validation constraint for time_field.
 *
 * Validates that the submitted value can be converted into a valid time object.
 *
 * @package Drupal\time_field\Plugin\Validation\Constraint
 */
class TimeConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    try {
      Time::createFromTimestamp($value);
    }
    catch (\InvalidArgumentException $e) {
      $this->context->addViolation(TimeConstraint::$message, []);
    }
  }

}
