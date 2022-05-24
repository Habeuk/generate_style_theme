<?php

namespace Drupal\generate_style_theme\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ValidateThemeCreate constraint.
 */
class ValidateThemeCreateValidator extends ConstraintValidator {
  
  /**
   *
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    // dump($this->context);
    foreach ($items as $item) {
      /**
       *
       * @var \Drupal\Core\Field\FieldItemList $item
       */
      // dump($item);
      if (!empty($item->value)) {
        // $this->context->addViolation($constraint->notThemeExiste, [
        // '%value' => $item->value
        // ]);
      }
    }
  }
  
}