<?php

namespace Drupal\generate_style_theme\Services\Reposotories;

use Stephane888\Debug\debugLog;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\File\FileSystem;
use Drupal\Core\File\FileSystemInterface;

trait GenerateFiles {
  /**
   *
   * @var FileSystem
   */
  protected $FileSystem;
  
  /**
   * --
   */
  function InfoYml() {
    $string = 'name: ' . $this->themeName . '
type: theme
description: " Theme Generate by generate_style_theme "
core_version_requirement: ^9 || ^10
base theme: ' . $this->baseTheme . '

regions:
  top_header: "Top header"
  header: "header"
  hero_slider: "Hero slider"
  sidebar_left: "Sidebar left"
  sidebar_right: "Sidebar right"
  before_content: "beforeContent"
  content: "content"
  after_content: "afterContent"
  call_to_action: "Call to action"
  footer: "Footer"

# Ajout des librairies
libraries:
  - ' . $this->themeName . '/vendor-style
  - ' . $this->themeName . '/global-style
# Supprimer les librairies du themes parent.
libraries-override:
  ' . $this->baseTheme . '/global-style: false';
    $filename = $this->themeName . '.info.yml';
    $path = $this->themePath . '/' . $this->themeName;
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
  }
  
  /**
   * --
   */
  function LibrairiesYml() {
    $string = 'global-style:
  css:
    theme:
      css/global-style.css: {}
  js:
    js/global-style.js: { preprocess: false }
  dependencies:
    - core/once

vendor-style:
  css:
    theme:
      css/vendor-style.css: {}
  js:
    js/vendor-style.js: { preprocess: false }
';
    $filename = $this->themeName . '.libraries.yml';
    $path = $this->themePath . '/' . $this->themeName;
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
  }
  
  /**
   * --
   */
  function jsFiles() {
    $this->getGlobalStyle();
    $this->getVendorStyle();
  }
  
  /**
   * On importe le fichier scss qui a été generé et les fichiers js qui sont
   * dans la config du theme.
   */
  private function getGlobalStyle() {
    $string = $this->buildEntityImportStyle('js') . "\n";
    $string .= 'import "../scss/' . $this->themeName . '.scss";';
    $string .= "\n";
    $filename = 'global-style.js';
    $path = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme/src/js';
    // $st = $this->FileSystem->prepareDirectory($path,
    // FileSystemInterface::CREATE_DIRECTORY |
    // FileSystemInterface::MODIFY_PERMISSIONS);
    // if (!$st) {
    // \Drupal::messenger()->addError("dir is not writable");
    // }
    // on cree un fichier pour le style custom, le fichier n'existe pas;
    if (!file_exists($path . '/custom.js')) {
      debugLog::logger("", "custom.js", false, 'file', $path, true);
    }
    $string .= 'import "./custom.js";';
    //
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
  }
  
  private function getVendorStyle() {
    $vendor_import = $this->generate_style_themeSettings['tab1']['vendor_import']['js'];
    $string = $vendor_import . '
      // On recupere le fichier scss generer precedament.
      import "../scss/' . $this->themeName . '--vendor.scss";
    ';
    $filename = 'vendor-style.js';
    $path = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme/src/js';
    // $st = $this->FileSystem->prepareDirectory($path,
    // FileSystemInterface::CREATE_DIRECTORY |
    // FileSystemInterface::MODIFY_PERMISSIONS);
    // if (!$st) {
    // \Drupal::messenger()->addError("dir is not writable");
    // }
    // $this->FileSystem->chmod($path, '777');
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
  }
  
  function RunNpm() {
    $pathNpm = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme';
    $build_mode = $this->generate_style_themeSettings['tab1']['build_mode'];
    $npm = 'npm';
    if (!empty($this->generate_style_themeSettings['tab1']['pwd_npm']))
      $npm = $this->generate_style_themeSettings['tab1']['pwd_npm'];
    $script = ' ';
    // if (!empty($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'],
    // ".kksa") !== false) {
    // $script .= " npm --prefix " . $pathNpm . " run " . $build_mode;
    // }
    // else {
    // $script .= " /homez.707/lesroig/tools/node15/bin/npm --prefix " .
    // $pathNpm . " run " . $build_mode;
    // }
    $script .= $npm . " --prefix " . $pathNpm . " run " . $build_mode;
    $exc = $this->excuteCmd($script, 'RunNpm');
    if ($exc['return_var']) {
      \Drupal::messenger()->addError(" Impossible de generer le theme NPM Error ");
      $this->logger->warning('NPM Error : <br>' . implode("<br>", $exc['output']));
    }
  }
  
  /**
   * Les liens symbolique ne marge pas.
   * On va faire un lien, Car cela est plus facile à gerer et occupe moins
   * d'espace.
   */
  function CopyWbuAtomiqueTheme() {
    $modulePath = DRUPAL_ROOT . "/" . $this->pathResolver->getPath('module', "generate_style_theme") . "/wbu-atomique-generate-theme";
    // $script = ' cp -r ' . $modulePath . ' ' . $this->themePath . '/' .
    // $this->themeName;
    // $script .= " && ";
    $script = null;
    $cmd = "ls " . $modulePath;
    $outputs = '';
    $return_var = '';
    exec($cmd . " 2>&1", $outputs, $return_var);
    // On copie tous les fichiers present dans wbu-atomique-generate-theme, sauf
    // le dossier node_modules.
    if ($return_var === 0) {
      $script = ' mkdir ' . $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme';
      foreach ($outputs as $output) {
        if ($output !== 'node_modules') {
          if ($script) {
            $script .= ' && cp -r ' . $modulePath . '/' . $output . ' ' . $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme';
          }
          else
            $script .= ' cp -r ' . $modulePath . '/' . $output . ' ' . $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme';
        }
      }
    }
    else {
      throw new \Exception(' Impossible de lire le contenu des fichiers  ');
    }
    // on fait un lien symbolique avec node_modules.
    $modulePath = DRUPAL_ROOT . "/" . $this->pathResolver->getPath('theme', $this->baseTheme) . "/wbu-atomique-theme";
    $script .= ' && ln -s ' . $modulePath . '/node_modules   ' . $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme/';
    $exc = $this->excuteCmd($script, 'CopyWbuAtomiqueTheme');
    if ($exc['return_var']) {
      $this->logger->warning('Error de copie des fichiers : <br>' . implode("<br>", $exc['output']));
    }
  }
  
  /**
   * --
   */
  function DeleteFilesNpm() {
    $script = ' rm -rf ' . $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme';
    $exc = $this->excuteCmd($script, 'CopyWbuAtomiqueTheme');
    if ($exc['return_var']) {
      $this->logger->warning('Error lors de la suppression de /wbu-atomique-theme : <br>' . implode("<br>", $exc['output']));
    }
  }
  
  /**
   * --
   */
  function scssFiles() {
    $this->buildVariables();
    $this->scssFilesGlobalStyle();
    $this->scssFilesVendorStyle();
    $this->scssFilesFromArray();
  }
  
  /**
   * Permet de construire un fchier avec des styles ajoutés dans un tableau.
   * Les styles peuveant provenir de la surcharge sur
   */
  private function scssFilesFromArray() {
  }
  
  /**
   * On genere le fichier de variable.
   */
  private function buildVariables() {
    $string = '';
    $string .= $this->buildScssVar();
    // Cree le fichier.
    $filename = $this->themeName . '_variables.scss';
    $path = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme/src/scss';
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
  }
  
  /**
   * On genere le fichier contenant les imports provenant des layouts et des
   * modules.
   */
  private function scssFilesVendorStyle() {
    $string = '@use "' . $this->getFileNameFileVariable() . '" as *;';
    if (empty($this->generate_style_themeSettings['tab1']['vendor_import']['load_custom_in_vendor'])) {
      $string .= $this->initLoader();
      $string .= $this->generate_style_themeSettings['tab1']['vendor_import']['scss'];
    }
    // Cree le fichier.
    $filename = $this->themeName . '--vendor.scss';
    $path = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme/src/scss';
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
  }
  
  /**
   * On genere le fichier contenant les imports provenant des layouts et
   * modules.
   * De plus, si load_custom_in_vendor est à true on doit generer un seul
   * fichier.
   */
  private function scssFilesGlobalStyle() {
    $string = '@use "' . $this->getFileNameFileVariable() . '" as *;';
    // pour charger un seul fichier.
    if (!empty($this->generate_style_themeSettings['tab1']['vendor_import']['load_custom_in_vendor'])) {
      $string .= $this->initLoader();
    }
    // On importe les styles definit de maniere automatique.
    $styleImport = $this->buildEntityImportStyle('scss');
    if (!empty($styleImport)) {
      $string .= $styleImport;
    }
    /**
     * On chargera toujours le fichier custom.scss ici.
     */
    $string .= '@use "./custom.scss";';
    // pour charger un seul fichier.
    if (!empty($this->generate_style_themeSettings['tab1']['vendor_import']['load_custom_in_vendor'])) {
      $string .= $this->generate_style_themeSettings['tab1']['vendor_import']['scss'];
    }
    
    // Cree le fichier.
    $filename = $this->themeName . '.scss';
    $path = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme/src/scss';
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
    // on cree un fichier pour le style custom, si le fichier n'existe pas;
    if (!file_exists($path . '/custom.scss')) {
      debugLog::logger("", "custom.scss", false, 'file', $path, true);
    }
  }
  
  /**
   * Construit les variables.
   *
   * @return string
   */
  private function buildScssVar() {
    $string = '';
    /**
     *
     * @var \Drupal\generate_style_theme\Entity\ConfigThemeEntity $entity
     */
    $entity = $this->entity;
    $color_primary = !empty($entity->getColorPrimary()['color']) ? $entity->getColorPrimary()['color'] : '#c69c6d';
    $color_secondaire = !empty($entity->getColorSecondaire()['color']) ? $entity->getColorSecondaire()['color'] : '#130f13';
    $wbu_color_thirdly = !empty($entity->getColorThirdly()['color']) ? $entity->getColorThirdly()['color'] : '#130f13';
    $color_background = !empty($entity->getColorBackground()['color']) ? $entity->getColorBackground()['color'] : '#192028';
    $wbu_h1_font_size = isset($entity->getH1FontSize()['value']) ? $entity->getH1FontSize()['value'] : '3.4rem';
    $wbu_h2_font_size = isset($entity->getH2FontSize()['value']) ? $entity->getH2FontSize()['value'] : '2.4rem';
    $wbu_h3_font_size = !empty($entity->getH3FontSize()) ? $entity->getH3FontSize() : '1.8rem';
    $wbu_h4_font_size = !empty($entity->getH4FontSize()) ? $entity->getH4FontSize() : '1.6rem';
    $string .= '
    // On a besoin de ce fichier pour les styles ajoutés dans ./custom.scss.
    // @use "@stephane888/wbu-atomique/scss/wbu-ressources-clean.scss" as *;
    ';
    $wbu_h5_font_size = !empty($entity->getH5FontSize()) ? $entity->getH5FontSize() : '1.4rem';
    $wbu_h6_font_size = !empty($entity->getH6FontSize()) ? $entity->getH6FontSize() : '1.4rem';
    $text_font_size = isset($entity->gettext_font_size()['value']) ? $entity->gettext_font_size()['value'] : '1.4rem';
    $space_bottom = isset($entity->getspace_bottom()['value']) ? $entity->getspace_bottom()['value'] : '5';
    $space_top = isset($entity->getspace_top()['value']) ? $entity->getspace_top()['value'] : '4';
    $space_inner_top = isset($entity->getspace_inner_top()['value']) ? $entity->getspace_inner_top()['value'] : '0.5';
    if (!empty($entity->getwbu_titre_big())) {
      $string .= '
$wbu_titre_big: ' . $entity->getwbu_titre_big() . ';';
    }
    if (!empty($entity->getwbu_titre_suppra())) {
      $string .= '
$wbu_titre_suppra: ' . $entity->getwbu_titre_suppra() . ';';
    }
    if (!empty($entity->getwbu_titre_biggest())) {
      $string .= '
$wbu_titre_biggest: ' . $entity->getwbu_titre_biggest() . ';';
    }
    $wbu_link_color = $entity->getScssColorValue($entity->getColorLink());
    $wbu_bootstrap_primary = $entity->getScssColorValue($entity->getBootstrapColorPrimary());
    
    return '
    /**
     * On definie les variables à ce niveau afin que les variables qui derive de ces valeurs soit ajusté.
     * Example : $wbu-h1-font-size est definie ici, les derivées $wbu-h1-font-size-md, $wbu-h1-font-size-sm vont etre
     * egalement surcharger.
     */

    //color
    $wbu-color-primary: ' . $color_primary . ';
    $wbu-color-secondary: ' . $color_secondaire . ';
    $wbu-color-thirdly: ' . $wbu_color_thirdly . ';
    $wbu-background: ' . $color_background . ';
    $wbu-link-color: ' . $wbu_link_color . ';
    $wbu-bootstrap-primary: ' . $wbu_bootstrap_primary . ';

    // Police
    $wbu-h1-font-size: ' . $wbu_h1_font_size . ';
    $wbu-h2-font-size: ' . $wbu_h2_font_size . ';
    $wbu-h3-font-size: ' . $wbu_h3_font_size . ';
    $wbu-h4-font-size: ' . $wbu_h4_font_size . ';
    $wbu-h5-font-size: ' . $wbu_h5_font_size . ';
    $wbu-h6-font-size: ' . $wbu_h6_font_size . ';
    $wbu-default-font-size: ' . $text_font_size . ';
    ' . $string . '

    /**
     * On injecte toutes les variables directement dans ce fichier.
     */
    @import "@stephane888/wbu-atomique/scss/_variables.scss";
    @import "@stephane888/wbu-atomique/scss/wbu-ressources-clean.scss";

    // Les variables qui ont besoins des informations provenant du core de
    // wbu-atomique.
    $space_bottom: $wbu-margin * ' . $space_bottom . ';
    $space_top: $wbu-margin * ' . $space_top . ';
    $space_inner_top: $space_top * ' . $space_inner_top . ';
    $space_inner_top: $space_top * 0.5;
';
  }
  
  /**
   * Permet de recuperer les données de styles.
   */
  private function buildEntityImportStyle(string $type = 'scss') {
    $styleToImport = '';
    if ($this->configKeyThemeSettings) {
      $editConfigTheme = \Drupal::config($this->configKeyThemeSettings);
      $EntityImport = $editConfigTheme->get('layoutgenentitystyles.' . $type);
      if (is_array($EntityImport))
        $styleToImport = $this->buildEntityImport($EntityImport);
    }
    return $styleToImport;
  }
  
  /**
   *
   * @param array $EntityImport
   * @return string
   */
  private function buildEntityImport(array $EntityImport) {
    $styleToImport = '';
    if (!empty($EntityImport)) {
      $libraries = [];
      // On parcourt les entites
      foreach ($EntityImport as $Entity) {
        // On parcourt les types d'entites ou plugins.
        if (is_array($Entity))
          foreach ($Entity as $view_mode) {
            // On parcourt les modes d'affichages.
            if (is_array($view_mode))
              foreach ($view_mode as $plugin) {
                // On parcourt les plugins.
                if (is_array($plugin))
                  foreach ($plugin as $plugin_id => $library) {
                    if (!empty($library) && is_array($library))
                      $libraries[$plugin_id] = implode("\n", $library);
                    // dump($libraries);
                  }
              }
          }
      }
      $styleToImport = implode("\n", $libraries);
    }
    return $styleToImport;
  }
  
  private function excuteCmd($cmd, $name = "excuteCmd") {
    ob_start();
    $return_var = '';
    $output = '';
    exec($cmd . " 2>&1", $output, $return_var);
    $result = ob_get_contents();
    ob_end_clean();
    $debug = [
      'output' => $output,
      'return_var' => $return_var,
      'result' => $result,
      'script' => $cmd
    ];
    return $debug;
  }
  
  protected function getFileNameFileVariable() {
    return './' . $this->themeName . '_variables.scss';
  }
  
  /**
   * Les styles par defaut qui permettent d'initialiser le theme.
   *
   * @return string
   */
  protected function initLoader() {
    $string = '@use "@stephane888/wbu-atomique/scss/bootstrap-all.scss";
@use "@stephane888/wbu-atomique/scss/atome/typography/_default.scss";
@use "@stephane888/wbu-atomique/scss/molecule/default-class.scss";
@use "@stephane888/wbu-atomique/scss/drupal/ajustement.scss";';
    return $string;
  }
  
}
