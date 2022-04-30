<?php

namespace Drupal\generate_style_theme\Services;

use Drupal\generate_style_theme\Services\Reposotories\GenerateFiles;
use CaseConverter\CaseString;
use Drupal\generate_style_theme\Entity\ConfigThemeEntity;

class GenerateStyleTheme {
  protected $themeName;
  protected $themeDirectory;
  protected $themePath;
  protected $entity;
  /**
   * Chemin vers l'executable NPM.
   *
   * @var Object
   * @deprecated
   */
  protected $npm;
  
  use GenerateFiles;
  
  /**
   *
   * @param Array $configs
   * @param ConfigThemeEntity $entity
   */
  function __construct($configs, ConfigThemeEntity $entity) {
    if (!is_string($configs['themeName'])) {
      Throw new \Exception("Le nom du theme n'est pas valide : " . $configs['themeName']);
    }
    $this->themeName = $configs['themeName'];
    $this->themeDirectory = CaseString::camel($configs['themeName'])->camel();
    $this->themePath = $this->getPath();
    $this->entity = $entity;
    // $this->createInstanceNpm();
  }
  
  /**
   *
   * @deprecated
   */
  function createInstanceNpm() {
    $ar = explode("/", DRUPAL_ROOT);
    $pr = '';
    for ($i = 0; $i < count($ar) - 1; $i++) {
      if (!empty($ar[$i])) {
        $pr .= "/" . $ar[$i];
      }
    }
    $this->npm = $pr . '/node/bin/npm';
    if (!file_exists($this->npm)) {
      \Drupal::messenger()->addError(" Le module node n'est pas installé, veillez telecharger nodejs, decompresser et placer les fichiers dans le public/node ");
    }
  }
  
  /**
   * Return le chemin vers le dossier parent du theme definit par defaut.
   * ( Logiquement il devrait pointer sur custom, pour que tous les themes soit disponible dans custom ).
   *
   * @throws \Exception
   * @return string
   */
  protected function getPath() {
    $defaultThemeName = \Drupal::config('system.theme')->get('default');
    $path_of_module = DRUPAL_ROOT . '/' . drupal_get_path('theme', $defaultThemeName);
    $path_of_module = explode("/", $path_of_module);
    //
    if (!empty($path_of_module)) {
      $path = '';
      for ($i = 0; $i < count($path_of_module) - 1; $i++) {
        if ($path_of_module[$i])
          $path .= "/" . $path_of_module[$i];
      }
      return $path;
    }
    throw new \Exception(" Impossible de determiner le chemin vers le dossier du theme. ");
  }
  
  /**
   *
   * @param boolean $createThme
   */
  function buildSubTheme($createThme = false) {
    $this->InfoYml();
    $this->LibrairiesYml();
    if ($createThme)
      $this->CopyWbuAtomiqueTheme();
    $this->scssFiles();
    $this->jsFiles();
    // $this->CopyWbuAtomiqueTheme();
    $this->RunNpm();
    $this->SetCurrentThemeDefaultOfDomaine();
    $this->setLogoToTheme();
  }
  
  protected function setLogoToTheme() {
    $domaineId = $this->entity->getHostname();
    if ($domaineId) {
      // On ajoute le logo.
      $keyDomain = 'domain.config.' . $domaineId . '.' . $domaineId . '.settings';
      /**
       *
       * @var \Drupal\Core\Config\ConfigFactoryInterface $editConfigTheme
       */
      $editConfigTheme = \Drupal::service('config.factory')->getEditable($keyDomain);
      $pathLogo = $this->entity->getLogo();
      if (!empty($pathLogo)) {
        $editConfigTheme->set('logo.path', $pathLogo)->save();
        $editConfigTheme->set('logo.use_default', 0)->save();
      }
    }
  }
  
  /**
   * Permet de definir le theme selectionner comme theme par defaut pour le domaine choisie et applique quelques paramettre de configurations.
   * Cette configuration est etroitement lier au module domain.
   * Dans le module domaine, il ya déjà une logique de surchage de la configuration qui est mis en place. voir sous module domain_config et domain_config_ui
   * Les partterns suivant peuvent etre utiliser : domain.config.DOMAIN_MACHINE_NAME.LANGCODE.item.name, domain.config.DOMAIN_MACHINE_NAME.item.name
   * example : system.site => domain.config.v2lesroisdelareno_kksa.system.site ( pour le domaine v2lesroisdelareno_kksa ).
   */
  protected function SetCurrentThemeDefaultOfDomaine() {
    $baseConfig = 'system.theme';
    
    $domaineId = $this->entity->getHostname();
    if ($domaineId) {
      $listThemes = \Drupal::service('theme_handler')->listInfo();
      // $listThemesInstalled = \Drupal::config("core.extension")->get('theme');
      // /**
      // *
      // * @var \Drupal\Core\Extension\ThemeExtensionList $ExtLitThemes
      // */
      $ExtLitThemes = \Drupal::service('extension.list.theme');
      $ExtLitThemes->reset();
      // dump($ExtLitThemes->getList());
      // dump($listThemesInstalled);
      
      /**
       * On installe le nouveau theme.
       */
      if (empty($listThemes[$domaineId])) {
        \Drupal::messenger()->addStatus(' Theme installé : ' . $domaineId);
        /**
         *
         * @var \Drupal\generate_style_theme\Services\Themes\ActiveAsignService $ActiveAsignService
         */
        $ActiveAsignService = \Drupal::service('generate_style_theme.active_asign');
        $ActiveAsignService->ActiveThemeForDomaine([
          $domaineId => $domaineId
        ]);
      }
      else {
        \Drupal::messenger()->addStatus(' Theme deja installé : ' . $domaineId);
      }
      $key = 'domain.config.' . $domaineId . '.' . $baseConfig;
      $configs = \Drupal::config($key);
      $defaultThemeName = $configs->get('default');
      
      // On definit le theme comme theme par defaut pour le nouveau theme.
      if ($domaineId != $defaultThemeName) {
        /**
         *
         * @var \Drupal\Core\Config\ConfigFactoryInterface $editConfig
         */
        $editConfig = \Drupal::service('config.factory')->getEditable($key);
        $editConfig->set('default', $domaineId)->save();
        \Drupal::messenger()->addStatus(' Theme definie par defaut : ' . $key);
      }
    }
  }
  
}