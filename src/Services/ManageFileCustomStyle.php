<?php

namespace Drupal\generate_style_theme\Services;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ExtensionPathResolver;
use Stephane888\Debug\debugLog;
use Stephane888\Debug\Repositories\ConfigDrupal;
use Drupal\Core\Form\FormStateInterface;

class ManageFileCustomStyle extends ControllerBase {
  
  /**
   *
   * @var string
   */
  protected $path;
  protected $pathScss;
  protected $pathJs;
  public $theme_name;
  
  /**
   *
   * @var ExtensionPathResolver
   */
  protected $ExtensionPathResolver;
  
  function __construct(ExtensionPathResolver $ExtensionPathResolver) {
    $this->ExtensionPathResolver = $ExtensionPathResolver;
  }
  
  /**
   *
   * @return string
   */
  protected function getPath() {
    if (!$this->path) {
      if (!$this->theme_name) {
        $conf = ConfigDrupal::config('system.theme');
        $this->theme_name = $conf['default'];
      }
      $this->path = DRUPAL_ROOT . '/' . $this->ExtensionPathResolver->getPath('theme', $this->theme_name) . '/wbu-atomique-theme/src';
    }
    return $this->path;
  }
  
  /**
   * Ecrase le contenu scss existant
   *
   * @param string $string
   */
  public function saveScss($string) {
    debugLog::logger($string, "custom.scss", false, 'file', $this->getPath() . '/scss', true);
  }
  
  /**
   * Ecrase le contenu js existant
   *
   * @param string $string
   */
  public function saveJs($string) {
    debugLog::logger($string, "custom.js", false, 'file', $this->getPath() . '/js', true);
  }
  
  public function getScss() {
    if (!$this->pathScss) {
      $this->pathScss = $this->getPath() . '/scss/custom.scss';
      if (!file_exists($this->pathScss))
        debugLog::logger("", "custom.scss", false, 'file', $this->getPath() . '/scss', true);
    }
    return file_get_contents($this->pathScss);
  }
  
  public function getJs() {
    if (!$this->pathJs) {
      $this->pathJs = $this->getPath() . '/js/custom.js';
      if (!file_exists($this->pathJs))
        debugLog::logger("", "custom.js", false, 'file', $this->getPath() . '/js', true);
    }
    return file_get_contents($this->pathJs);
  }
  
}