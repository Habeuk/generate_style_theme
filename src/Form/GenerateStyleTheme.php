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
    if (\Drupal::moduleHandler()->moduleExists('domain'))
      $form['use_domain'] = [
        '#type' => 'checkbox',
        '#title' => 'Generer css&js themes à partir du domaine',
        '#description' => "",
        '#default_value' => $config->get('tab1.use_domain')
      ];
    
    $form['save_multifile'] = [
      '#type' => 'checkbox',
      '#title' => "Genere les fichiers scss & js(node) par entité",
      '#default_value' => $config->get('tab1.save_multifile'),
      '#description' => " Permet de creer un fichier scss et js par entité à chaque fois que cest necessaire."
    ];
    $form['run_node'] = [
      '#type' => 'checkbox',
      '#title' => "Genere les fichiers css et js utilisable par le theme",
      '#default_value' => $config->get('tab1.run_node'),
      '#description' => " En mode developpement, vous devez decoché cette option et lancer un <strong>npm run Dev:custom --custom node__page__52</strong> ou <strong>npm run Dev:custom --custom node__page__52</strong> <br> 
      NB: node__page__52 => doit etre ajuster en fonction de votre page "
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
      '#type' => 'fieldset',
      '#tree' => true,
      '#open' => true,
      '#title' => 'Styles vendor scss & js'
    ];
    $form['vendor_import']['scss'] = [
      '#type' => 'textarea',
      '#title' => 'Contient les imports scss par defaut',
      '#default_value' => $config->get('tab1.vendor_import.scss'),
      '#description' => " Les imports definit doivent commencer par @use, la configuration serra automatiquement appliquer. <br>
      Mais vous pourriez appliquer une configuration tant qu'elle ne coïncider avec celle par defaut ",
      '#attributes' => [
        'class' => [
          'codemirror',
          'lang_scss'
        ]
      ]
    ];
    $form['vendor_import']['js'] = [
      '#type' => 'textarea',
      '#title' => 'Contient les imports js par defaut',
      '#default_value' => $config->get('tab1.vendor_import.js'),
      '#attributes' => [
        'class' => [
          'codemirror',
          'lang_js'
        ]
      ]
    ];
    $form['vendor_import']['load_custom_in_vendor'] = [
      '#type' => 'checkbox',
      '#title' => "Utiliser un seul fichier scss, cela permet d'utiliser @extend.",
      '#default_value' => $config->get('tab1.vendor_import.load_custom_in_vendor'),
      '#description' => "Cette action permet de generer un seul fichier css pour le site et d'utiliser efficacement la proproité @extend"
    ];
    $form['#attached']['library'][] = 'layout_custom_style/codemirror_admin';
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
    if (\Drupal::moduleHandler()->moduleExists('domain'))
      $config->set('tab1.use_domain', $form_state->getValue('use_domain'));
    $config->set('tab1.save_multifile', $form_state->getValue('save_multifile'));
    $config->set('tab1.run_node', $form_state->getValue('run_node'));
    $config->set('tab1.build_mode', $form_state->getValue('build_mode'));
    $config->set('tab1.vendor_import', $form_state->getValue('vendor_import'));
    $config->set('tab1.pwd_npm', $form_state->getValue('pwd_npm'));
    $config->save();
  }
  
}