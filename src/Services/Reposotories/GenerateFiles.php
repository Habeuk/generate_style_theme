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
core: 8.x
core_version_requirement: ^8 || ^9
base theme: ' . $this->baseTheme . '

regions:
  top_header: "Top header"
  header: "Header"
  hero_slider: "Hero slider"
  content: "Contenu principal"
  sidebar_left: "sidebar left"
  call_to_action: "Call to action"
  footer: "Footer"
  commerce_sidebar_left: "commerce sidebar left"

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
    js/global-style.js: {}
  dependencies:
    - core/once

vendor-style:
  css:
    theme:
      css/vendor-style.css: {}
  js:
    js/vendor-style.js: {}
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
    // import "@stephane888/wbu-atomique/js/swiper/swiper-big-v3.js";
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
    else {
      // \Drupal::messenger()->addStatus(" Fichier du theme generer avec
      // success, veuillez utiliser CTRL+F5 ");
    }
  }
  
  /**
   * On copie les fichiers.
   *
   * @deprecated
   */
  function CopyWbuAtomiqueThemeOld() {
    // wbu-atomique-theme/src/js
    $modulePath = DRUPAL_ROOT . "/" . drupal_get_path('module', "generate_style_theme") . "/wbu-atomique-theme";
    $script = ' cp -r ' . $modulePath . ' ' . $this->themePath . '/' . $this->themeName;
    $this->excuteCmd($script, 'CopyWbuAtomiqueTheme');
  }
  
  /**
   * Les liens symbolique ne marge pas.
   * On va faire un lien, Car cela est plus facile à gerer et occupe moins
   * d'espace.
   */
  function CopyWbuAtomiqueTheme() {
    // wbu-atomique-theme/src/js
    $modulePath = DRUPAL_ROOT . "/" . drupal_get_path('module', "generate_style_theme") . "/wbu-atomique-theme";
    // $script = ' cp -r ' . $modulePath . ' ' . $this->themePath . '/' .
    // $this->themeName;
    // $script .= " && ";
    $script = null;
    $cmd = "ls " . $modulePath;
    $outputs = '';
    $return_var = '';
    exec($cmd . " 2>&1", $outputs, $return_var);
    // On copie tous les fichiers present dans wbu-atomique-theme, sauf le
    // dossier node_modules.
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
    $modulePath = DRUPAL_ROOT . "/" . drupal_get_path('theme', $this->baseTheme) . "/wbu-atomique-theme";
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
    $this->scssFilesGlobalStyle();
    $this->scssFilesVendorStyle();
  }
  
  private function scssFilesVendorStyle() {
    $vendor_import = $this->generate_style_themeSettings['tab1']['vendor_import']['scss'];
    // On charge les mixins et les variables.
    $string = '';
    $string .= $this->buildScssVar();
    $string .= '
    @use "@stephane888/wbu-atomique/scss/wbu-ressources-clean.scss" as *;
    ';
    $string .= $vendor_import;
    
    // Cree le fichier.
    $filename = $this->themeName . '--vendor.scss';
    $path = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme/src/scss';
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
  }
  
  /**
   * --
   */
  private function scssFilesGlobalStyle() {
    /**
     *
     * @var \Drupal\generate_style_theme\Entity\ConfigThemeEntity $entity
     */
    $entity = $this->entity;
    
    // On charge les mixins et les variables.
    $string = '';
    // Ce modele est gardé pour etre compatible avec le site les roisdelareno.
    // if (\Drupal::moduleHandler()->moduleExists('wbumenudomain')) {
    // $libray = !empty($entity->getLirairy()['value']) ?
    // $entity->getLirairy()['value'] : 'lesroisdelareno/prestataires_m0';
    // $confs = $this->getScssFromLibrairy($libray);
    // $string .= $confs['configs'] . $this->buildScssVar();
    // $string .= $confs['files'];
    // }
    // else {
    // $string .= $this->buildScssVar();
    // $string .= $this->buildEntityImportScss();
    // }
    $string .= $this->buildScssVar();
    $string .= ' @use "@stephane888/wbu-atomique/scss/wbu-ressources-clean.scss" as *; ';
    $styleImport = $this->buildEntityImportStyle('scss');
    if (!empty($styleImport)) {
      $string .= $styleImport;
      $string .= '@use "./custom.scss";';
    }
    else {
      // Ce modele est gardé pour etre compatible avec le site lesroisdelareno.
      if (\Drupal::moduleHandler()->moduleExists('wbumenudomain')) {
        $libray = !empty($entity->getLirairy()['value']) ? $entity->getLirairy()['value'] : 'lesroisdelareno/prestataires_m0';
        $confs = $this->getScssFromLibrairy($libray);
        $string .= $confs['configs'] . $this->buildScssVar();
        $string .= $confs['files'];
      }
    }
    // Cree le fichier.
    $filename = $this->themeName . '.scss';
    $path = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme/src/scss';
    
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
    // on cree un fichier pour le style custom, le fichier n'existe pas;
    if (!file_exists($path . '/custom.scss')) {
      debugLog::logger("", "custom.scss", false, 'file', $path, true);
    }
  }
  
  private function buildScssVar() {
    $string = '';
    /**
     *
     * @var \Drupal\generate_style_theme\Entity\ConfigThemeEntity $entity
     */
    $entity = $this->entity;
    $color_primary = isset($entity->getColorPrimary()['color']) ? $entity->getColorPrimary()['color'] : '#c69c6d';
    $color_secondaire = isset($entity->getColorSecondaire()['color']) ? $entity->getColorSecondaire()['color'] : '#130f13';
    $color_link_hover = isset($entity->getColorLinkHover()['color']) ? $entity->getColorLinkHover()['color'] : '#130f13';
    $color_background = isset($entity->getColorBackground()['color']) ? $entity->getColorBackground()['color'] : '#192028';
    $wbu_h1_font_size = isset($entity->getH1FontSize()['value']) ? $entity->getH1FontSize()['value'] : '3.4rem';
    $wbu_h2_font_size = isset($entity->getH2FontSize()['value']) ? $entity->getH2FontSize()['value'] : '2.4rem';
    $wbu_h3_font_size = !empty($entity->getH3FontSize()) ? $entity->getH3FontSize() : '1.8rem';
    $wbu_h4_font_size = !empty($entity->getH4FontSize()) ? $entity->getH4FontSize() : '1.6rem';
    $wbu_h5_font_size = !empty($entity->getH5FontSize()) ? $entity->getH5FontSize() : '1.4rem';
    $wbu_h6_font_size = !empty($entity->getH6FontSize()) ? $entity->getH6FontSize() : '1.4rem';
    $text_font_size = isset($entity->gettext_font_size()['value']) ? $entity->gettext_font_size()['value'] : '1.4rem';
    $space_bottom = isset($entity->getspace_bottom()['value']) ? $entity->getspace_bottom()['value'] : '5';
    $space_top = isset($entity->getspace_top()['value']) ? $entity->getspace_top()['value'] : '4';
    $space_inner_top = isset($entity->getspace_inner_top()['value']) ? $entity->getspace_inner_top()['value'] : '0.5';
    if (!empty($entity->getwbu_titre_big())) {
      $string .= '$wbu_titre_big: ' . $entity->getwbu_titre_big() . ';';
    }
    if (!empty($entity->getwbu_titre_suppra())) {
      $string .= '$wbu_titre_suppra: ' . $entity->getwbu_titre_suppra() . ';';
    }
    if (!empty($entity->getwbu_titre_biggest())) {
      $string .= '$wbu_titre_biggest: ' . $entity->getwbu_titre_biggest() . ';';
    }
    
    return '
    @use "@stephane888/wbu-atomique/scss/_variables.scss" as *;
    $wbu-color-primary: ' . $color_primary . ';
    $wbu-color-secondary: ' . $color_secondaire . ';
    $wbu-color-thirdly: ' . $color_link_hover . ';
    // supprimer de wbu-atomique
    //$wbu-color-link-hover: ' . $color_link_hover . ';
    $wbu-background: ' . $color_background . ';
    $wbu-h1-font-size: ' . $wbu_h1_font_size . ';
    $wbu-h1-font-size-md: $wbu-h1-font-size * 0.8;
    $wbu-h1-font-size-sm: $wbu-h1-font-size * 0.7;
    $wbu-h2-font-size: ' . $wbu_h2_font_size . ';
    $wbu-h2-font-size-sm: $wbu-h2-font-size * 0.8;
    $wbu-h3-font-size: ' . $wbu_h3_font_size . ';
    $wbu-h3-font-size-sm: $wbu-h3-font-size * 0.8;
    $wbu-h4-font-size: ' . $wbu_h4_font_size . ';
    $wbu-h5-font-size: ' . $wbu_h5_font_size . ';
    $wbu-h6-font-size: ' . $wbu_h6_font_size . ';
    $space_bottom: $wbu-margin * ' . $space_bottom . ';
    $space_top: $wbu-margin * ' . $space_top . ';
    //$space_inner_top: $space_top * ' . $space_inner_top . ';
    $space_inner_top: $space_top * 0.5;
    $wbu-default-font-size: ' . $text_font_size . ';
    ' . $string;
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
                    if (!empty($library))
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
  
  protected function getScssFromLibrairy($libray) {
    if ($libray == 'lesroisdelareno/prestataires_m8')
      return [
        'files' => '
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m8/prestataires-default.scss";
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m8/prestataires.scss";

        ',
        'configs' => '@use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m8/variables.scss";'
      ];
    elseif ($libray == 'lesroisdelareno/prestataires_m0')
      return [
        'files' => '
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m0/prestataires-m0-default.scss";
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m0/prestataires-m0.scss";
        ',
        'configs' => '@use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/variables.scss";'
      ];
    elseif ($libray == 'lesroisdelareno/prestataires_m1')
      return [
        'files' => '
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m1/prestataires-m1-default.scss";
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m1/prestataires-m1.scss";
        ',
        'configs' => '@use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m1/variables.scss";'
      ];
    elseif ($libray == 'lesroisdelareno/prestataires_m2')
      return [
        'files' => '
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m2/prestataires-m2-default.scss";
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m2/prestataires-m2.scss";
        ',
        'configs' => '@use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m2/variables.scss";'
      ];
    elseif ($libray == 'lesroisdelareno/prestataires_m3')
      return [
        'files' => '
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m3/prestataires-m3-default.scss";
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m3/prestataires-m3.scss";
        ',
        'configs' => '@use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m3/variables.scss";'
      ];
    elseif ($libray == 'lesroisdelareno/prestataires_m4')
      return [
        'files' => '
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m4/prestataires-m4-default.scss";
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m4/prestataires-m4.scss";
        ',
        'configs' => '@use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m4/variables.scss";'
      ];
    elseif ($libray == 'lesroisdelareno/prestataires_m5')
      return [
        'files' => '
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m5/prestataires-m5-default.scss";
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m5/prestataires-m5.scss";
        ',
        'configs' => '@use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m5/variables.scss";'
      ];
    elseif ($libray == 'lesroisdelareno/prestataires_m6')
      return [
        'files' => '
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m6/prestataires-m6-default.scss";
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m6/prestataires-m6.scss";
        ',
        'configs' => '@use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m6/variables.scss";'
      ];
    elseif ($libray == 'lesroisdelareno/prestataires_m7')
      return [
        'files' => '
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m7/prestataires-m7-default.scss";
          @use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m7/prestataires-m7.scss";
        ',
        'configs' => '@use "' . $this->themePath . '/lesroisdelareno/wbu-atomique-theme/src/scss/m7/variables.scss";'
      ];
    else
      throw new \Exception(" Fichier de style scss non definit ");
  }
  
}