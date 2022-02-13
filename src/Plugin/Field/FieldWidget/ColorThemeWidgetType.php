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
 *   id = "color_theme_widget_type",
 *   module = "generate_style_theme",
 *   label = @Translation("Color theme widget type"),
 *   field_types = {
 *     "color_theme_field_type"
 *   }
 * )
 */
class ColorThemeWidgetType extends ColorapiWidgetBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // $element['#type'] = 'details';
    // $element['#open'] = true;
    $element += parent::formElement($items, $delta, $element, $form, $form_state);
    // $element['color'] = [
    // '#type' => 'textfield',
    // '#title' => $this->t('Color'),
    // '#default_value' => isset($items[$delta]) ? $items[$delta]->getHexadecimal() : '',
    // '#cardinality' => $this->fieldDefinition->getFieldStorageDefinition()->getCardinality()
    // ];
    $element['color']['#type'] = 'color';
    $element['name']['#access'] = false;
    $element['color']['#title'] = $element['#title'];
    
    return $element;
  }
  
}
