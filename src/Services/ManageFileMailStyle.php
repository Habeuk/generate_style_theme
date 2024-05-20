<?php

namespace Drupal\generate_style_theme\Services;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ExtensionPathResolver;
use Stephane888\Debug\debugLog;
use Stephane888\Debug\Repositories\ConfigDrupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\generate_style_theme\Entity\FilesMailStyle;

class ManageFileMailStyle extends ControllerBase {
  
  /**
   *
   * @var string
   */
  protected $path;
  
  /**
   * Permet de definir un theme autre que celui encours pour la sauvegarde.
   *
   * @var string
   */
  public $theme_name;
  
  /**
   *
   * @var ExtensionPathResolver
   */
  protected $ExtensionPathResolver;
  /**
   *
   * @var array
   */
  protected $generate_style_themeSettings = [];
  
  function __construct(ExtensionPathResolver $ExtensionPathResolver) {
    $this->ExtensionPathResolver = $ExtensionPathResolver;
  }
  
  /**
   * --
   *
   * @return string
   */
  protected function getPath() {
    if (!$this->path) {
      $this->getSelectedTheme();
      $this->path = DRUPAL_ROOT . '/' . $this->ExtensionPathResolver->getPath('theme', $this->theme_name) . '/wbu-atomique-theme/src';
    }
    return $this->path;
  }
  
  /**
   * --
   *
   * @return string
   */
  protected function getSelectedTheme() {
    if (!$this->theme_name) {
      $conf = ConfigDrupal::config('system.theme');
      $this->theme_name = $conf['default'];
    }
    return $this->theme_name;
  }
  
  public function getConfigGenerateStyleTheme() {
    if (!$this->generate_style_themeSettings) {
      $this->generate_style_themeSettings = ConfigDrupal::config('generate_style_theme.settings');
    }
    return $this->generate_style_themeSettings;
  }
  
  /**
   * Permet d'enregistrer les styles pour un module.
   * Utiliser principalement pour les styles de surcharge.
   *
   * @param string $key
   * @param string $module
   * @param string $scss
   * @param array $customValue,
   *        permet de passer des valeurs specique unqiuement lors de la
   *        creation.
   */
  public function saveStyle($key, $module, $scss, $customValue = []) {
    $entity = FilesMailStyle::loadByName($key, $module);
    if ($entity) {
      $entity->setScss($scss);
      $entity->save();
    }
    else {
      $values = [
        'label' => $key,
        'module' => $module,
        'scss' => $scss
      ] + $customValue;
      $entity = FilesMailStyle::create($values);
      $entity->save();
    }
    $this->generateCustomFile();
  }
  
  /**
   * Permet de supprimer un style.
   *
   * @param string $key
   * @param string $module
   */
  public function deleteStyle($key, $module) {
    $entity = FilesMailStyle::loadByName($key, $module);
    if ($entity) {
      $entity->delete();
    }
  }
  
  public function generateCustomFile() {
    $entities = FilesMailStyle::loadMultiple();
    $variable_file = './' . $this->getSelectedTheme() . '_variables.scss';
    $scss = '    @use "' . $variable_file . '" as *;    ';
    // On charge les ressources de base.
    $scss .= '
    @use "@stephane888/wbu-atomique/scss/wbu-ressources-clean.scss" as *;
   ';
    
    foreach ($entities as $entity) {
      // add comment
      $scss .= "\n";
      $scss .= "// module : " . $entity->getModule() . ' || ' . $entity->label();
      $scss .= " \n";
      $scss .= $entity->getScss();
    }
    debugLog::logger($scss, "mail-style.scss", false, 'file', $this->getPath() . '/scss', true);
  }
  
  /**
   * Permet de recuperer le style d'un module.
   *
   * @return string|boolean
   */
  public function getScss($key, $module) {
    $entity = FilesMailStyle::loadByName($key, $module);
    if ($entity) {
      return $entity->getScss();
    }
  }
  
}