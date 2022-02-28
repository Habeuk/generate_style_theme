<?php

namespace Drupal\generate_style_theme\Services;

use Drupal\Core\Field\PluginSettingsInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Stephane888\Debug\debugLog;
use Drupal\block_content\Entity\BlockContent;
use Drupal\node\Entity\node;
use Drupal\generate_style_theme\Entity\ContentCreateAutomaticaly;

/**
 * Permet de dupliquer les contenus, si elle n'existe pas.
 *
 * @author stephane
 *        
 */
class CreateEntityFromWidget {
  protected static $field_domain_access = 'field_domain_access';
  
  /**
   *
   * @param PluginSettingsInterface $widget
   * @param ContentEntityInterface $entity
   * @param FieldItemListInterface $field
   */
  public function createEntity(PluginSettingsInterface $widget, ContentEntityInterface &$entity, FieldItemListInterface $field, ContentCreateAutomaticaly &$storageCreateAutomaticaly) {
    debugLog::$max_depth = 4;
    $values = $field->getValue();
    $settings = $field->getSettings();
    if ($settings['handler'] == 'default:block_content') {
      $entity->get($field->getName())->setValue($this->createBlockContent($values, $entity, $storageCreateAutomaticaly));
    }
    elseif ($settings['handler'] == 'default:node') {
      $entity->get($field->getName())->setValue($this->createNode($values, $entity, $storageCreateAutomaticaly));
    }
  }
  
  /**
   *
   * @param array $values
   * @param ContentEntityInterface $entity
   * @return string[][]|number[][]|NULL[][]
   */
  protected function createNode(array $values, ContentEntityInterface $entity, ContentCreateAutomaticaly &$contentCreateAutomaticaly) {
    $newValues = [];
    foreach ($values as $value) {
      $node = node::load($value['target_id']);
      if ($node) {
        $cloneNode = $node->createDuplicate();
        // On ajoute le champs field_domain_access; ci-possible.
        if ($cloneNode->hasField(self::$field_domain_access) && $entity->hasField(self::$field_domain_access)) {
          $cloneNode->get(self::$field_domain_access)->setValue($entity->get(self::$field_domain_access)->getValue());
        }
        $cloneNode->save();
        $newValues[] = [
          'target_id' => $cloneNode->id()
        ];
        $contentCreateAutomaticaly->AddSubContentToPage($entity->bundle(), $cloneNode->id());
      }
    }
    return $newValues;
  }
  
  /**
   *
   * @param array $values
   */
  protected function createBlockContent(array $values, ContentEntityInterface $entity, ContentCreateAutomaticaly &$contentCreateAutomaticaly) {
    $newValues = [];
    foreach ($values as $value) {
      $block = BlockContent::load($value['target_id']);
      if ($block) {
        $cloneBlock = $block->createDuplicate();
        // On ajoute le champs field_domain_access; ci-possible.
        if ($cloneBlock->hasField(self::$field_domain_access) && $entity->hasField(self::$field_domain_access)) {
          $dmn = $entity->get(self::$field_domain_access)->first()->getValue();
          $dmn = empty($dmn['target_id']) ? null : [
            'value' => $dmn['target_id']
          ];
          if ($dmn)
            $cloneBlock->get(self::$field_domain_access)->setValue($dmn);
        }
        // on met à jour le champs info (car sa valeur doit etre unique).
        if ($cloneBlock->hasField("info")) {
          $val = $cloneBlock->get('info')->first()->getValue();
          $dmn = $entity->get(self::$field_domain_access)->first()->getValue();
          $dmn = empty($dmn['target_id']) ? 'domaine.test' : $dmn['target_id'];
          if (!empty($val['value']))
            $val = $val['value'] . ' -- ' . $dmn . ' -- ' . $entity->bundle() . rand(1, 999);
          
          $cloneBlock->get('info')->setValue([
            'value' => $val
          ]);
        }
        
        $cloneBlock->save();
        
        $newValues[] = [
          'target_id' => $cloneBlock->id()
        ];
        $contentCreateAutomaticaly->AddSubBlockToPage($entity->bundle(), $cloneBlock->id());
      }
    }
    return $newValues;
  }
  
}