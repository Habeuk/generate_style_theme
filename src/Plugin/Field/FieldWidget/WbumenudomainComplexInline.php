<?php

namespace Drupal\wbumenudomain\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\wbumenudomain\Wbumenudomain;

use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;

/**
 * A widget bar.
 *
 * @FieldWidget(
 *   id = "wbumenudomainhost_complex_inline",
 *   label = @Translation(" Wbumenudomain Complexe inline "),
 *   field_types = {
 *     "entity_reference",
 *     "entity_reference_revisions",
 *   },
 *   multiple_values = true
 * )
 */
class WbumenudomainComplexInline extends InlineEntityFormComplex {
  
  /**
   *
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element += parent::formElement($items, $delta, $element, $form, $form_state);
    // $entityTypeId = $items->getEntity()->bundle();
    
    if ($items->getFieldDefinition()->getName() == "field_logo_clients_nos_clients_00") {
      
      /**
       *
       * @var \Drupal\node\Entity\Node $node
       */
      $node = \Drupal::entityTypeManager()->getStorage('node')->load(235);
      $cloneNode = $node->createDuplicate();
      if ($items->getEntity()->isNew() && !empty($element['entities'])) {
        $addNode = [];
        $addNode['#label'] = $this->inlineFormHandler->getEntityLabel($cloneNode);
        $addNode['#entity'] = $cloneNode;
        $addNode['#needs_save'] = true;
        $addNode['#weight'] = 0;
        $addNode['title'] = [];
        $element['entities'][] = $addNode;
        // dump($addNode);
      }
    }
    
    return $element;
  }
  
  /**
   * Le rendu inline_entity_form_complex a explicitement desactivé la valeur par defaut.
   *
   * L'objectif est de definir des valeurs par defaut.
   *
   * {@inheritdoc}
   * @see \Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormBase::form()
   */
  // public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
  // if (!$this->isDefaultValueWidget($form_state))
  // return parent::form($items, $form, $form_state, $get_delta);
  // else {
  // return parent::form($items, $form, $form_state, $get_delta);
  // }
  // }
  
  // protected function canBuildForm(FormStateInterface $form_state) {
  // return true;
  // }
  
  /**
   *
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settingForm = parent::settingsForm($form, $form_state);
    // dump($settingForm);
    return $settingForm;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settingsSummary = parent::settingsSummary();
    // dump($settingsSummary);
    return $settingsSummary;
  }
  
  public function _getIefId() {
    return $this->getIefId();
  }
  
}