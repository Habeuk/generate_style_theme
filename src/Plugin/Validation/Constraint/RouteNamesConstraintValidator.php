<?php

namespace Drupal\generate_style_theme\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\generate_style_theme\Services\RouteNamesValidatorService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Validates the RouteNames constraint.
 */
class RouteNamesConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  protected $routeNamesValidator;

  public function __construct(RouteNamesValidatorService $route_names_validator) {
    $this->routeNamesValidator = $route_names_validator;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('generate_style_theme.route_names_validator')
    );
  }

  public function validate($value, Constraint $constraint) {
    if (empty($value->value)) {
      return;
    }

    $errors = $this->routeNamesValidator->validate($value->value);
    
    foreach ($errors as $error) {
      $this->context->addViolation($error);
    }
  }
}