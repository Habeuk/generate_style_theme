<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete Contenu creer automatiquement entities.
 */
class ContentCreateAutomaticalyDeleteForm extends EntityConfirmFormBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', [
      '%name' => $this->entity->label()
    ]);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.content_create_automaticaly.collection');
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t(' Supprimer le domaine et tout son contenu ');
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    
    $this->messenger()->addMessage($this->t('content @type: deleted @label.', [
      '@type' => $this->entity->bundle(),
      '@label' => $this->entity->label()
    ]));
    
    $form_state->setRedirectUrl($this->getCancelUrl());
  }
  
}
