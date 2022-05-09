<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Formulaire pour la configuration de mon module
 */
class GenerateStyleTheme extends ConfigFormBase {
  private static $key = 'generate_style_theme.settings';
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return self::$key;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::$key
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::$key);
    $form = parent::buildForm($form, $form_state);
    $form['theme_base'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Theme de base'),
      '#default_value' => $config->get('tab1.theme_base')
    ];
    $form['use_domain'] = [
      '#type' => 'checkbox',
      '#title' => 'Generer css&js themes à partir du domaine',
      '#default_value' => $config->get('tab1.use_domain')
    ];
    return parent::buildForm($form, $form_state);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config(self::$key);
    $config->set('tab1.theme_base', $form_state->getValue('theme_base'));
    $config->set('tab1.use_domain', $form_state->getValue('use_domain'));
    $config->save();
  }
  
}