<?php

namespace Drupal\generate_style_theme\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "generate_style_theme_example",
 *   admin_label = @Translation("Example"),
 *   category = @Translation("Generate style theme")
 * )
 */
class ExampleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    return $build;
  }

}
