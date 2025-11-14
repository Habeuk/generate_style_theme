<?php

namespace Drupal\generate_style_theme\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for route names.
 *
 * @Constraint(
 *   id = "RouteNames",
 *   label = @Translation("Route names validation", context = "Validation"),
 * )
 */
class RouteNamesConstraint extends Constraint {

  /**
   * The error message for invalid route names.
   *
   * @var string
   */
  public $invalidRoutes = 'The following routes are invalid: %routes. Each line must contain a valid route name.';

  /**
   * The error message for duplicate route names.
   *
   * @var string
   */
  public $duplicateRoutes = 'Duplicate routes found: %routes.';

}