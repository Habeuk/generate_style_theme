<?php

namespace Drupal\generate_style_theme\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\colorapi\Plugin\Field\FieldFormatter\ColorapiColorDisplayFormatter;

/**
 * Plugin implementation of the 'color_theme_formatter_type' formatter.
 *
 * @FieldFormatter(
 *   id = "generate_style_theme_color_formatter",
 *   label = @Translation("Color theme formatter type"),
 *   field_types = {
 *     "generate_style_theme_color"
 *   }
 * )
 */
class ColorThemeFormatterType extends ColorapiColorDisplayFormatter {

}
