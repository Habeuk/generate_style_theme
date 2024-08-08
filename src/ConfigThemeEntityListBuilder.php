<?php

namespace Drupal\generate_style_theme;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Config theme entity entities.
 *
 * @ingroup generate_style_theme
 */
class ConfigThemeEntityListBuilder extends EntityListBuilder {
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Config theme entity ID');
    $header['name'] = $this->t('Name');
    $header['create'] = $this->t('Create date');
    return $header + parent::buildHeader();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\generate_style_theme\Entity\ConfigThemeEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute($entity->label(), 'entity.config_theme_entity.edit_form', [
      'config_theme_entity' => $entity->id()
    ]);
    $row['created'] = \Drupal::service('date.formatter')->format($entity->get('created')->value, 'date_text');
    
    return $row + parent::buildRow($entity);
  }
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Entity\EntityListBuilder::render()
   */
  public function render() {
    $build = parent::render();
    // On ajoute lien pour l'edition avec filtre.
    $build['views_list'] = [
      '#type' => 'html_tag',
      '#tag' => 'a',
      '#value' => 'Utiliser la liste avec filtre',
      '#weight' => -10,
      '#attributes' => [
        'href' => '/model-de-site-web'
      ]
    ];
    return $build;
  }
  
}
