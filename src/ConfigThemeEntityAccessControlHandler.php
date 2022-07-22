<?php

namespace Drupal\generate_style_theme;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Config theme entity entity.
 *
 * @see \Drupal\generate_style_theme\Entity\ConfigThemeEntity.
 */
class ConfigThemeEntityAccessControlHandler extends EntityAccessControlHandler {
  
  /**
   *
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\generate_style_theme\Entity\ConfigThemeEntityInterface $entity */
    switch ($operation) {
      
      case 'view':
        
        if (!$entity->isPublished()) {
          if ($account->id() == $entity->getOwnerId()) {
            return AccessResult::allowed();
          }
          return AccessResult::allowedIfHasPermission($account, 'view unpublished config theme entity entities');
        }
        
        return AccessResult::allowedIfHasPermission($account, 'view published config theme entity entities');
      
      case 'update':
        
        return AccessResult::allowedIfHasPermission($account, 'edit config theme entity entities');
      
      case 'delete':
        
        return AccessResult::allowedIfHasPermission($account, 'delete config theme entity entities');
    }
    
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add config theme entity entities');
  }
  
}
