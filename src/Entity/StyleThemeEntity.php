<?php

namespace Drupal\generate_style_theme\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Style de theme entity.
 *
 * @ConfigEntityType(
 *   id = "style_theme_entity",
 *   label = @Translation("Style de theme"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\generate_style_theme\StyleThemeEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\generate_style_theme\Form\StyleThemeEntityForm",
 *       "edit" = "Drupal\generate_style_theme\Form\StyleThemeEntityForm",
 *       "delete" = "Drupal\generate_style_theme\Form\StyleThemeEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\generate_style_theme\StyleThemeEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "style_theme_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/style_theme_entity/{style_theme_entity}",
 *     "add-form" = "/admin/structure/style_theme_entity/add",
 *     "edit-form" = "/admin/structure/style_theme_entity/{style_theme_entity}/edit",
 *     "delete-form" = "/admin/structure/style_theme_entity/{style_theme_entity}/delete",
 *     "collection" = "/admin/structure/style_theme_entity"
 *   }
 * )
 */
class StyleThemeEntity extends ConfigEntityBase implements StyleThemeEntityInterface {

  /**
   * The Style de theme ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Style de theme label.
   *
   * @var string
   */
  protected $label;

}
