<?php

namespace Drupal\wbumenudomain\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_example_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "wbumenudomaineditlink",
 *   module = "wbumenudomain",
 *   label = @Translation("wbumenudomaineditlink formatter"),
 *   field_types = {
 *     "wbumenudomaineditlink"
 *   }
 * )
 */
class Wbumenudomaineditlink extends FormatterBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        // We create a render array to produce the desired markup,
        // "<p style="color: #hexcolor">The color code ... #hexcolor</p>".
        // See theme_html_tag().
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#attributes' => [],
        '#value' => $item->value
      ];
    }
    return $elements;
  }
  
}
