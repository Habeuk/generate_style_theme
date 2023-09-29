<?php

namespace Drupal\generate_style_theme\Services;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ExtensionPathResolver;
use Stephane888\Debug\debugLog;
use Stephane888\Debug\Repositories\ConfigDrupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\generate_style_theme\Entity\FilesStyle;

class ManageFileCustomStyle extends ControllerBase {
  
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
  
  /**
   * Ecrase le contenu scss existant.
   * L'idee est de permettre à plusieurs modules d'ajouter du contenu dans le
   * fichier. Pour ce faire on va passer par une entité.
   *
   * @param string $string
   * @deprecated car il faudra enregistrer le scss et le js dans la meme entité.
   *             Use saveStyle()
   */
  public function saveScss($string, $key, $module) {
    $entity = FilesStyle::loadByName($key, $module);
    if ($entity) {
      $entity->setScss($string);
      $entity->save();
    }
    else {
      $values = [
        'label' => $key,
        'module' => $module,
        'scss' => $string
      ];
      $entity = FilesStyle::create($values);
      $entity->save();
    }
    $this->generateCustomFile();
  }
  
  /**
   * Ecrase le contenu js existant
   *
   * @param string $string
   * @deprecated car il faudra enregistrer le scss et le js dans la meme entité.
   *             Use saveStyle()
   *            
   */
  public function saveJs($string, $key, $module) {
    $entity = FilesStyle::loadByName($key, $module);
    if ($entity) {
      $entity->setScss($string);
      $entity->save();
    }
    else {
      $values = [
        'label' => $key,
        'module' => $module,
        'scss' => $string
      ];
      $entity = FilesStyle::create($values);
      $entity->save();
    }
    $this->generateCustomFile();
  }
  
  /**
   * Permet d'enregistrer les styles pour un module.
   * Utiliser principalement pour les styles de surcharge.
   *
   * @param string $key
   * @param string $module
   * @param string $scss
   * @param string $js
   * @param array $customValue,
   *        permet de passer des valeurs specique unqiuement lors de la
   *        creation.
   */
  public function saveStyle($key, $module, $scss, $js, $customValue = []) {
    $entity = FilesStyle::loadByName($key, $module);
    if ($entity) {
      $entity->setScss($scss);
      $entity->setJs($js);
      $entity->save();
    }
    else {
      $values = [
        'label' => $key,
        'module' => $module,
        'scss' => $scss,
        'js' => $js
      ] + $customValue;
      $entity = FilesStyle::create($values);
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
    $entity = FilesStyle::loadByName($key, $module);
    if ($entity) {
      $entity->delete();
    }
  }
  
  public function generateCustomFile() {
    $entities = FilesStyle::loadMultiple();
    $variable_file = './' . $this->getSelectedTheme() . '_variables.scss';
    $scss = '    @use "' . $variable_file . '" as *;    ';
    $js = '';
    foreach ($entities as $entity) {
      $scss .= $entity->getScss();
      $js .= $entity->getJs();
    }
    debugLog::logger($js, "custom.js", false, 'file', $this->getPath() . '/js', true);
    debugLog::logger($scss, "custom.scss", false, 'file', $this->getPath() . '/scss', true);
  }
  
  /**
   * Permet de recuperer le style d'un module.
   *
   * @return string|boolean
   */
  public function getScss($key, $module) {
    $entity = FilesStyle::loadByName($key, $module);
    if ($entity) {
      return $entity->getScss();
    }
  }
  
  /**
   * Permet de recuperer le style d'un module.
   *
   * @return string|boolean
   */
  public function getJs($key, $module) {
    $entity = FilesStyle::loadByName($key, $module);
    if ($entity) {
      return $entity->getJs();
    }
  }
  
}