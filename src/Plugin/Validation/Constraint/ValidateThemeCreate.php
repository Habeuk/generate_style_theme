<?php

namespace Drupal\generate_style_theme\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a unique integer.
 *
 * @Constraint(
 *   id = "ValidateThemeCreate",
 *   label = @Translation("ValidateThemeCreate", context = "Validation"),
 *   type = "string"
 * )
 */
class ValidateThemeCreate extends Constraint {
  // The message that will be shown if the value is not an integer.
  public $notThemeExiste = "%value : le theme n'a pas pu etre creer. <br> Verifiez les droits d'access au repertoire themes/custom et assurer vous que le groupe d'utilisateur 'www-data' peut y ecrire. ";
  
}