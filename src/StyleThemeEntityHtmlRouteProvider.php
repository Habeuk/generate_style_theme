<?php

namespace Drupal\generate_style_theme;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;

/**
 * Provides routes for Style de theme entities.
 *
 * @see Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class StyleThemeEntityHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    // Provide your custom entity routes here.
    return $collection;
  }

}
