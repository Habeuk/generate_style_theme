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
    // $form = parent::buildForm($form, $form_state);
    $form['theme_base'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Theme de base'),
      '#default_value' => $config->get('tab1.theme_base')
    ];
    $form['use_domain'] = [
      '#type' => 'checkbox',
      '#title' => 'Generer css&js themes à partir du domaine',
      '#description' => "",
      '#default_value' => $config->get('tab1.use_domain')
    ];
    $form['save_multifile'] = [
      '#type' => 'checkbox',
      '#title' => "Genere plusieurs fichier en function de l'entité",
      '#default_value' => $config->get('tab1.save_multifile')
    ];
    $form['build_mode'] = [
      '#type' => 'select',
      '#title' => 'Mode de creation de fichiers css et js',
      '#options' => [
        'ProdCMD' => 'Production (les fichiers sont zippées)',
        'DevCMD' => 'Developpment '
      ],
      '#default_value' => $config->get('tab1.build_mode')
    ];
    $form['pwd_npm'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chemin vers la commande npm'),
      '#default_value' => $config->get('tab1.pwd_npm'),
      '#description' => " Utiliser la commande 'whereis npm' afin de determiner le chemin "
    ];
    $form['vendor_import'] = [
      '#type' => 'details',
      '#tree' => true,
      '#open' => true,
      '#title' => 'Styles vendor scss & js'
    ];
    $form['vendor_import']['scss'] = [
      '#type' => 'textarea',
      '#title' => 'Contient les imports scss par defaut',
      '#default_value' => $config->get('tab1.vendor_import.scss'),
      '#description' => " Les imports definit doivent commencer par @use, la configuration serra automatiquement appliquer. <br>
      Mais vous pourriez appliquer une configuration tant qu'elle ne conside avec celle par defaut "
    ];
    $form['vendor_import']['js'] = [
      '#type' => 'textarea',
      '#title' => 'Contient les imports js par defaut',
      '#default_value' => $config->get('tab1.vendor_import.js')
    ];
    $form['vendor_import']['load_custom_in_vendor'] = [
      '#type' => 'checkbox',
      '#title' => "Charge le fichier custom dans vendor, cela permet d'utiliser @extent.",
      '#default_value' => $config->get('tab1.vendor_import.load_custom_in_vendor')
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
    $config->set('tab1.save_multifile', $form_state->getValue('save_multifile'));
    $config->set('tab1.build_mode', $form_state->getValue('build_mode'));
    $config->set('tab1.vendor_import', $form_state->getValue('vendor_import'));
    $config->set('tab1.pwd_npm', $form_state->getValue('pwd_npm'));
    $config->save();
  }

}