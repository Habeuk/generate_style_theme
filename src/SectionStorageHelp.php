<?php

namespace Drupal\generate_style_theme;

use Drupal\layout_builder\SectionStorage\SectionStorageDefinition;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;

/**
 * Cette page permet de sauvagarder les données d'un layout bien precis.
 *
 * @author stephane
 *        
 */
class SectionStorageHelp {
  
  /**
   * permet de recuperation l'objet
   */
  static public function getSectionStorage($plugin_id = 'defaults') {
    $definition = new SectionStorageDefinition([
      'id' => $plugin_id,
      'provider' => 'layout_builder',
      'class' => 'Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage'
    ]);
    $plugin = DefaultsSectionStorage::create(\Drupal::getContainer(), [], 'defaults', $definition);
    // $plugin->setContext('display', new Context(new ContextDefinition()));
    // $plugin->setContext('default', new Context(new ContextDefinition('string'), 'foobar'));
    return $plugin;
  }
  
}