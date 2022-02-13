<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StyleThemeEntityForm.
 */
class StyleThemeEntityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $style_theme_entity = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $style_theme_entity->label(),
      '#description' => $this->t("Label for the Style de theme."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $style_theme_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\generate_style_theme\Entity\StyleThemeEntity::load',
      ],
      '#disabled' => !$style_theme_entity->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $style_theme_entity = $this->entity;
    $status = $style_theme_entity->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Style de theme.', [
          '%label' => $style_theme_entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Style de theme.', [
          '%label' => $style_theme_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($style_theme_entity->toUrl('collection'));
  }

}
