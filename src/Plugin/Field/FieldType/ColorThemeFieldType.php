<?php

namespace Drupal\generate_style_theme\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\colorapi\Plugin\Field\FieldType\ColorItem;

/**
 * Plugin implementation of the 'color_theme_field_type' field type.
 *
 * @FieldType(
 *   id = "color_theme_field_type",
 *   label = @Translation("Color theme field type"),
 *   description = @Translation("permet de definir la couleur d'un theme"),
 *   default_widget = "color_theme_widget_type",
 *   default_formatter = "color_theme_formatter_type"
 * )
 */
class ColorThemeFieldType extends ColorItem {
  
  /**
   *
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [] + parent::defaultStorageSettings();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);
    return $properties;
  }
  
}
