<?php

namespace Drupal\wbumenudomain\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Core\Render\Element\Checkboxes;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\SortArray;

/**
 * A widget bar.
 *
 * @FieldWidget(
 *   id = "wbumenudomaineditmenu",
 *   label = @Translation(" Wbumenudomain EditMenu "),
 *   field_types = {
 *     "list_string",
 *     "list_integer"
 *   }
 * )
 */
class WbumenudomainEditMenu extends OptionsWidgetBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'menu' => 'main',
      'placeholder' => 'placeholder'
    ] + parent::defaultSettings();
  }
  
  public static function validateElement(array $element, FormStateInterface $form_state) {
    parent::validateElement($element, $form_state);
    $items = [
      [
        'value' => "hummm"
      ]
    ];
    // die();
    $form_state->setValueForElement($element, $items);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    
    // Extract the values from $form_state->getValues().
    $path = array_merge($form['#parents'], [
      $field_name
    ]);
    $key_exists = NULL;
    $values = NestedArray::getValue($form_state->getValues(), $path, $key_exists);
    
    // die();
    // Assign the values and remove the empty ones.
    $items->setValue($values);
    $items->filterEmptyItems();
    // die();
    // remove validation value with default in set in entity.
    if ($key_exists === '88 not filed' && empty($field_name)) {
      // Account for drag-and-drop reordering if needed.
      if (!$this->handlesMultipleValues()) {
        // Remove the 'value' of the 'add more' button.
        unset($values['add_more']);
        
        // The original delta, before drag-and-drop reordering, is needed to
        // route errors to the correct form element.
        foreach ($values as $delta => &$value) {
          $value['_original_delta'] = $delta;
        }
        
        usort($values, function ($a, $b) {
          return SortArray::sortByKeyInt($a, $b, '_weight');
        });
      }
      
      // Let the widget massage the submitted values.
      $values = $this->massageFormValues($values, $form, $form_state);
      
      // Assign the values and remove the empty ones.
      $items->setValue($values);
      $items->filterEmptyItems();
      
      // Put delta mapping in $form_state, so that flagErrors() can use it.
      $field_state = static::getWidgetState($form['#parents'], $field_name, $form_state);
      foreach ($items as $delta => $item) {
        $field_state['original_deltas'][$delta] = isset($item->_original_delta) ? $item->_original_delta : $delta;
        unset($item->_original_delta, $item->_weight);
      }
      static::setWidgetState($form['#parents'], $field_name, $form_state, $field_state);
    }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => t(' Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format. ')
    ];
    return $element;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $selected = $this->getSelectedOptions($items);
    // $this->options = $this->getMenuItems();
    
    // Build the element render array.
    // $element + [
    // '#type' => 'checkboxes',
    // '#default_value' => [],
    // '#placeholder' => $this->getSetting('placeholder'),
    // '#options' => $this->getMenuItems(),
    // '#allowed_values' => [
    // 'humm'
    // ],
    // '#attributes' => [
    // 'class' => []
    // ],
    // '#required' => true
    // ];
    
    $element += [
      '#type' => 'checkboxes',
      '#default_value' => $selected,
      '#allowed_values' => $this->getMenuItems(),
      '#options' => $this->getMenuItems()
    ];
    return $element;
  }
  
  /**
   * -
   */
  protected function getMenuItems() {
    $query = \Drupal::entityQuery('menu_link_content');
    $query->condition('bundle', 'main');
    $menuIds = $query->execute();
    $items = MenuLinkContent::loadMultiple($menuIds);
    $options = [];
    foreach ($items as $value) {
      $tired = !empty($value->getParentId()) ? ' -- ' : '';
      $options[$value->id()] = $tired . $value->getTitle();
    }
    return $options;
  }
  
}