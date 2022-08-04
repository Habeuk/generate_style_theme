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
      $conf = ConfigDrupal::config('system.theme');
      $this->path = DRUPAL_ROOT . '/' . $this->ExtensionPathResolver->getPath('theme', $conf['default']) . '/wbu-atomique-theme/src';
    }
    return $this->path;
  }
  
  public function saveScss($string) {
    debugLog::logger($string, "custom.scss", false, 'file', $this->getPath() . '/scss', true);
  }
  
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