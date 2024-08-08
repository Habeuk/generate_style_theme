<?php

/**
 * @file
 * Contains config_theme_entity.page.inc.
 *
 * Page callback for Config theme entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Config theme entity templates.
 *
 * Default template: config_theme_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_config_theme_entity(array &$variables) {
  // Fetch ConfigThemeEntity Entity Object.
  $config_theme_entity = $variables['elements']['#config_theme_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
