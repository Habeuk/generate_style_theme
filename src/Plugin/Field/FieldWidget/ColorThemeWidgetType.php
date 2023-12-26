<?php

namespace Drupal\generate_style_theme\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\colorapi\Plugin\Field\FieldWidget\ColorapiWidgetBase;

/**
 * Plugin implementation of the 'color_theme_widget_type' widget.
 *
 * @FieldWidget(
 *   id = "generate_style_theme_color_widget",
 *   module = "generate_style_theme",
 *   label = @Translation("Color theme widget type"),
 *   field_types = {
 *     "generate_style_theme_color"
 *   }
 * )
 */
class ColorThemeWidgetType extends ColorapiWidgetBase {

  /**
   *
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // $element['#type'] = 'fieldset';
    $element['color']['#type'] = 'color';
    $element['name']['#access'] = false;
    $element['color']['#title'] = $element['#title'];

    return $element;
  }

}
