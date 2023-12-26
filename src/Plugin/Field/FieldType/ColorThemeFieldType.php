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
 * Plugin implementation of the 'generate_style_theme_color' field type.
 *
 * @FieldType(
 *   id = "generate_style_theme_color",
 *   label = @Translation("Color theme"),
 *   description = @Translation("permet de definir la couleur d'un theme"),
 *   default_widget = "generate_style_theme_color_widget",
 *   default_formatter = "generate_style_theme_color_formatter"
 * )
 */
class ColorThemeFieldType extends ColorItem {

}
