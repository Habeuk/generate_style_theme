<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Stephane888\Debug\Repositories\ConfigDrupal;
use Stephane888\Debug\debugLog;
use Drupal\Core\Extension\ExtensionPathResolver;
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
   * @var ExtensionPathResolver
   */
  protected $ExtensionPathResolver;
  
  function __construct(ConfigFactoryInterface $config_factory, ExtensionPathResolver $ExtensionPathResolver) {
    parent::__construct($config_factory);
    $this->ExtensionPathResolver = $ExtensionPathResolver;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'), $container->get('extension.path.resolver'));
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
    $pathScss = $this->getPath() . '/scss/custom.scss';
    $pathJs = $this->getPath() . '/js/custom.js';
    //
    if (!file_exists($pathScss)) {
      debugLog::logger("", "custom.scss", false, 'file', $this->getPath() . '/scss', true);
    }
    if (!file_exists($pathJs)) {
      debugLog::logger("", "custom.js", false, 'file', $this->getPath() . '/js', true);
    }
    //
    $form['file_scss'] = [
      '#type' => 'textarea',
      '#title' => $this->t(' Custom scss '),
      '#default_value' => file_get_contents($pathScss)
    ];
    $form['file_js'] = [
      '#type' => 'textarea',
      '#title' => $this->t(' Custom js '),
      '#default_value' => file_get_contents($pathJs)
    ];
    //
    return parent::buildForm($form, $form_state);
  }
  
  /**
   *
   * @return string
   */
  protected function getPath() {
    if (!$this->path) {
      $conf = ConfigDrupal::config('system.theme');
      $this->path = DRUPAL_ROOT . '/' . $this->ExtensionPathResolver->getPath('theme', $conf['default']) . '/wbu-atomique-theme/src';
    }
    return $this->path;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_scss = $form_state->getValue('file_scss');
    $file_js = $form_state->getValue('file_js');
    debugLog::logger($file_scss, "custom.scss", false, 'file', $this->getPath() . '/scss', true);
    debugLog::logger($file_js, "custom.js", false, 'file', $this->getPath() . '/js', true);
  }
  
}