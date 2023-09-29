<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Stephane888\Debug\Repositories\ConfigDrupal;
use Stephane888\Debug\debugLog;
use Drupal\generate_style_theme\Services\ManageFileCustomStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Formulaire pour la configuration de mon module
 */
class GenerateStyleThemeStyles extends ConfigFormBase {
  private static $key = 'generate_style_theme.styles';
  /**
   *
   * @var string
   */
  protected $path;
  
  /**
   *
   * @var ManageFileCustomStyle
   */
  protected $ManageFileCustomStyle;
  
  function __construct(ConfigFactoryInterface $config_factory, ManageFileCustomStyle $ManageFileCustomStyle) {
    parent::__construct($config_factory);
    $this->ManageFileCustomStyle = $ManageFileCustomStyle;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'), $container->get('generate_style_theme.manage_file_custom_style'));
  }
  
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
    //
    $form['file_scss'] = [
      '#type' => 'textarea',
      '#title' => $this->t(' Custom scss '),
      '#default_value' => $this->ManageFileCustomStyle->getScss(self::$key, 'generate_style_theme'),
      '#row' => '30'
    ];
    $form['file_js'] = [
      '#type' => 'textarea',
      '#title' => $this->t(' Custom js '),
      '#default_value' => $this->ManageFileCustomStyle->getJs(self::$key, 'generate_style_theme')
    ];
    //
    return parent::buildForm($form, $form_state);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_scss = $form_state->getValue('file_scss');
    $file_js = $form_state->getValue('file_js');
    $this->ManageFileCustomStyle->saveStyle(self::$key, 'generate_style_theme', $file_scss, $file_js);
  }
  
}