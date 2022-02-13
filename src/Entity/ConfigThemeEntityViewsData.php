<?php

namespace Drupal\generate_style_theme\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Config theme entity entities.
 */
class ConfigThemeEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
