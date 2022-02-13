<?php

namespace Drupal\generate_style_theme;

class GenerateStyleTheme {
  
  /**
   * Returns the theme hook definition information.
   */
  public static function getThemeHooks() {
    $hooks['generate_style_theme_success'] = [
      'render element' => 'element'
    ];
    return $hooks;
  }
  
}