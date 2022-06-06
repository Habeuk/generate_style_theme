<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Config theme entity edit forms.
 *
 * @ingroup generate_style_theme
 */
class ConfigThemeEntityForm extends ContentEntityForm {
  
  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\generate_style_theme\Entity\ConfigThemeEntity $entity */
    $form = parent::buildForm($form, $form_state);
    return $form;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    
    $status = parent::save($form, $form_state);
    
    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t(' Created the %label Config theme entity. ', [
          '%label' => $entity->label()
        ]));
        break;
      
      default:
        $this->messenger()->addMessage($this->t('Saved the %label Config theme entity.', [
          '%label' => $entity->label()
        ]));
    }
    $form_state->setRedirect('entity.config_theme_entity.canonical', [
      'config_theme_entity' => $entity->id()
    ]);
  }
  
}
