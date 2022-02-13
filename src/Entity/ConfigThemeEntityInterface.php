<?php

namespace Drupal\generate_style_theme\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Config theme entity entities.
 *
 * @ingroup generate_style_theme
 */
interface ConfigThemeEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {
  
  /**
   * Add get/set methods for your configuration properties here.
   */
  
  /**
   * Retoune exactement l'id du domaine
   *
   * @return string Name of the Config theme entity.
   */
  public function getHostname();
  
  /**
   * Sets the Config theme entity name.
   *
   * @param string $name
   *        The Config theme entity name.
   *        
   * @return \Drupal\generate_style_theme\Entity\ConfigThemeEntityInterface The
   *         called Config theme entity entity.
   */
  public function setHostname($name);
  
  /**
   * Gets the Config theme entity creation timestamp.
   *
   * @return int Creation timestamp of the Config theme entity.
   */
  public function getCreatedTime();
  
  /**
   * Sets the Config theme entity creation timestamp.
   *
   * @param int $timestamp
   *        The Config theme entity creation timestamp.
   *        
   * @return \Drupal\generate_style_theme\Entity\ConfigThemeEntityInterface The
   *         called Config theme entity entity.
   */
  public function setCreatedTime($timestamp);
  
}
