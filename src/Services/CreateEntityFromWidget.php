<?php

namespace Drupal\generate_style_theme\Services;

use Drupal\Core\Field\PluginSettingsInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Stephane888\Debug\debugLog;
use Drupal\block_content\Entity\BlockContent;
use Drupal\node\Entity\node;

class CreateEntityFromWidget {
  protected static $field_domain_access = 'field_domain_access';
  
  /**
   *
   * @param PluginSettingsInterface $widget
   * @param ContentEntityInterface $entity
   * @param FieldItemListInterface $field
   */
  public function createEntity(PluginSettingsInterface $widget, ContentEntityInterface &$entity, FieldItemListInterface $field) {
    debugLog::$max_depth = 4;
    $values = $field->getValue();
    $settings = $field->getSettings();
    if ($settings['handler'] == 'default:block_content') {
      $entity->get($field->getName())->setValue($this->createBlockContent($values, $entity));
    }
    elseif ($settings['handler'] == 'default:node') {
      $entity->get($field->getName())->setValue($this->createNode($values, $entity));
    }
  }
  
  /**
   *
   * @param array $values
   * @param ContentEntityInterface $entity
   * @return string[][]|number[][]|NULL[][]
   */
  protected function createNode(array $values, ContentEntityInterface $entity) {
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
      }
    }
    return $newValues;
  }
  
  /**
   *
   * @param array $values
   */
  protected function createBlockContent(array $values, ContentEntityInterface $entity) {
    $newValues = [];
    foreach ($values as $value) {
      $block = BlockContent::load($value['target_id']);
      if ($block) {
        $status = 'vide';
        $cloneBlock = $block->createDuplicate();
        // On ajoute le champs field_domain_access; ci-possible.
        if ($cloneBlock->hasField(self::$field_domain_access) && $entity->hasField(self::$field_domain_access)) {
          $dmn = $entity->get(self::$field_domain_access)->first()->getValue();
          $dmn = empty($dmn['target_id']) ? null : [
            'value' => $dmn['target_id']
          ];
          if ($dmn)
            $cloneBlock->get(self::$field_domain_access)->setValue($dmn);
          $status = [
            'MAJ du champs : ' . self::$field_domain_access,
            $cloneBlock->get(self::$field_domain_access)
          ];
        }
        // on met à jour le champs info (car sa valeur doit etre unique).
        if ($cloneBlock->hasField("info")) {
          $val = $cloneBlock->get('info')->first()->getValue();
          $dmn = $entity->get(self::$field_domain_access)->first()->getValue();
          $dmn = empty($dmn['target_id']) ? 'domaine.test' : $dmn['target_id'];
          if (!empty($val['value']))
            $val = $val['value'] . ' -- ' . $dmn . ' -- ' . $entity->bundle();
          
          $cloneBlock->get('info')->setValue([
            'value' => $val
          ]);
        }
        
        $cloneBlock->save();
        // debugLog::kintDebugDrupal([
        // "cloneBlock" => $cloneBlock,
        // "cloneBlock->hasField" => $cloneBlock->hasField(self::$field_domain_access),
        // "cloneBlock->value" => $cloneBlock->get(self::$field_domain_access)->getValue(),
        // "entity" => $entity,
        // "entity->hasField" => $entity->hasField(self::$field_domain_access),
        // "entity->value" => $entity->get(self::$field_domain_access)->getValue(),
        // "entity->value:fist" => $entity->get(self::$field_domain_access)->first()->getValue(),
        // 'id' => $cloneBlock->id(),
        // 'status' => $status
        // ], "createBlockContent", true);
        $newValues[] = [
          'target_id' => $cloneBlock->id()
        ];
      }
    }
    return $newValues;
  }
  
}