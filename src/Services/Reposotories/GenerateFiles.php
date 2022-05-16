<?php

namespace Drupal\generate_style_theme\Services\Reposotories;

use Stephane888\Debug\debugLog;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

trait GenerateFiles {
  
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
    $string = '
      import "../scss/' . $this->themeName . '.scss";
    ';
    $filename = 'global-style.js';
    $path = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme/src/js';
    
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
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
  }
  
  function RunNpm() {
    $pathNpm = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme';
    $build_mode = $this->generate_style_themeSettings['tab1']['build_mode'];
    $script = '';
    // if (!empty($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'],
    // ".kksa") !== false) {
    // $script .= " npm --prefix " . $pathNpm . " run " . $build_mode;
    // }
    // else {
    // $script .= " /homez.707/lesroig/tools/node15/bin/npm --prefix " .
    // $pathNpm . " run " . $build_mode;
    // }
    $script .= " npm --prefix " . $pathNpm . " run " . $build_mode;
    $exc = $this->excuteCmd($script, 'RunNpm');
    if ($exc['return_var']) {
      \Drupal::messenger()->addError(" Impossible de generer le theme NPM Error ");
      $this->logger->warning('NPM Error : <br>' . implode("<br>", $exc['output']));
    }
    else {
      \Drupal::messenger()->addStatus(" Fichier du theme generer avec success, veuillez utiliser CTRL+F5 ");
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
    $this->excuteCmd($script, 'CopyWbuAtomiqueTheme');
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
    $string = '
    @use "@stephane888/wbu-atomique/scss/wbu-ressources-clean.scss" as *;
    ';
    $string .= $this->buildScssVar();
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
    $string = '
    @use "@stephane888/wbu-atomique/scss/wbu-ressources-clean.scss" as *;
    ';
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
    $styleImport = $this->buildEntityImportScss();
    if (!empty($styleImport)) {
      $string .= $styleImport;
    }
    else {
      // Ce modele est gardé pour etre compatible avec le site les roisdelareno.
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
  }
  
  private function buildScssVar() {
    $entity = $this->entity;
    $color_primary = !empty($entity->getColorPrimary()['color']) ? $entity->getColorPrimary()['color'] : '#c69c6d';
    $color_secondaire = !empty($entity->getColorSecondaire()['color']) ? $entity->getColorSecondaire()['color'] : '#130f13';
    $color_link_hover = !empty($entity->getColorLinkHover()['color']) ? $entity->getColorLinkHover()['color'] : '#130f13';
    $color_background = !empty($entity->getColorBackground()['color']) ? $entity->getColorBackground()['color'] : '#192028';
    $wbu_h1_font_size = !empty($entity->getH1FontSize()['value']) ? $entity->getH1FontSize()['value'] : '3.4rem';
    $wbu_h2_font_size = !empty($entity->getH2FontSize()['value']) ? $entity->getH2FontSize()['value'] : '2.4rem';
    $text_font_size = !empty($entity->gettext_font_size()['value']) ? $entity->gettext_font_size()['value'] : '1.4rem';
    $space_bottom = !empty($entity->getspace_bottom()['value']) ? $entity->getspace_bottom()['value'] : '5';
    $space_top = !empty($entity->getspace_top()['value']) ? $entity->getspace_top()['value'] : '4';
    $space_inner_top = !empty($entity->getspace_inner_top()['value']) ? $entity->getspace_inner_top()['value'] : '0.5';
    return '
    $wbu-color-primary: ' . $color_primary . '; 
    $wbu-color-secondary: ' . $color_secondaire . '; 
    $wbu-color-link-hover: ' . $color_link_hover . '; 
    $wbu-background: ' . $color_background . '; 
    $wbu-h1-font-size: ' . $wbu_h1_font_size . ';
    $wbu-h2-font-size: ' . $wbu_h2_font_size . ';
    $space_bottom: $wbu-margin * ' . $space_bottom . ';
    $space_top: $wbu-margin * ' . $space_top . ';
    $space_inner_top: $space_top * ' . $space_inner_top . ';
    $wbu-default-font-size: ' . $text_font_size . '; 
    ';
  }
  
  /**
   * Permet de recuperer les données de styles.
   */
  private function buildEntityImportScss() {
    $styleToImport = '';
    if ($this->configKeyThemeSettings) {
      $editConfigTheme = \Drupal::config($this->configKeyThemeSettings);
      $EntityImport = $editConfigTheme->get('layoutgenentitystyles.scss');
      // $defaultThemeName = \Drupal::config('system.theme')->get('default');
      // dump(\Drupal::config($defaultThemeName . '.settings')->getRawData());
      // dump($editConfigTheme->getRawData());
      // die();
      if (!empty($EntityImport)) {
        $libraries = [];
        // on parcourt les entites
        foreach ($EntityImport as $Entity) {
          // On parcourt les types d'entites ou plugins.
          if (is_array($Entity))
            foreach ($Entity as $view_mode) {
              // On parcourt les modes d'affichages.
              if (is_array($view_mode))
                foreach ($view_mode as $plugin) {
                  // on parcourt les plugins.
                  if (is_array($plugin))
                    foreach ($plugin as $plugin_id => $library) {
                      $libraries[$plugin_id] = implode("\n", $library);
                      // dump($libraries);
                    }
                }
            }
        }
        // dump($libraries);
        // die();
        $styleToImport = implode("\n", $libraries);
      }
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