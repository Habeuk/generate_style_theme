<?php

namespace Drupal\generate_style_theme\Services\Reposotories;

use Stephane888\Debug\debugLog;
use ScssPhp\ScssPhp\Compiler;

trait GenerateFiles {
  
  function InfoYml() {
    $string = 'name: ' . $this->themeName . '
type: theme
description: " Theme Generate by generate_style_theme "
core: 8.x
core_version_requirement: ^8 || ^9
base theme: lesroisdelareno
logo: logo.png
screenshot: lesroisdelareno.png

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
  - ' . $this->themeName . '/global-style
';
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
    $string = '
      // surcharge la scss.
      import "../scss/' . $this->themeName . '.scss";  
      import "bootstrap/dist/js/bootstrap.min.js";
      import "@stephane888/wbu-atomique/js/swiper/swiper-big-v3.js";
    ';
    $string .= 'var temps = ' . time() . ';';
    $string .= ' console.log(temps); ';
    $filename = 'global-style.js';
    $path = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme/src/js';
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
  }
  
  function RunNpm() {
    $pathNpm = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme';
    $script = ' cd  ' . $pathNpm;
    $script .= " && npm -v && ";
    // $script .= " && npm -v && ";
    $script .= " npm run ProdCMD ";
    $this->excuteCmd($script, 'RunNpm');
  }
  
  /**
   * .
   */
  function CopyWbuAtomiqueTheme() {
    // wbu-atomique-theme/src/js
    $modulePath = DRUPAL_ROOT . "/" . drupal_get_path('module', "generate_style_theme") . "/wbu-atomique-theme";
    $script = ' cp -r ' . $modulePath . '  ' . $this->themePath . '/' . $this->themeName;
    // $script .= " && ";
    $this->excuteCmd($script, 'CopyWbuAtomiqueTheme');
  }
  
  /**
   * --
   */
  function scssFiles() {
    /**
     *
     * @var \Drupal\generate_style_theme\Entity\ConfigThemeEntity $entity
     */
    $entity = $this->entity;
    $color_primary = !empty($entity->getColorPrimary()['color']) ? $entity->getColorPrimary()['color'] : '#c69c6d';
    $color_secondaire = !empty($entity->getColorSecondaire()['color']) ? $entity->getColorSecondaire()['color'] : '#130f13';
    $color_link_hover = !empty($entity->getColorLinkHover()['color']) ? $entity->getColorLinkHover()['color'] : '#130f13';
    $libray = !empty($entity->getLirairy()['value']) ? $entity->getLirairy()['value'] : 'lesroisdelareno/prestataires_m0';
    $confs = $this->getScssFromLibrairy($libray);
    $string = '  
    @use "@stephane888/wbu-atomique/scss/wbu-ressources-clean.scss" as *;
    ';
    $string .= $confs['configs'] . '
    $wbu-color-primary: ' . $color_primary . '; 
    $wbu-color-secondary: ' . $color_secondaire . '; 
    $wbu-color-link-hover: ' . $color_link_hover . '; 
    $wbu-h2-font-size: 3.4rem;
    $wbu-color-link: $wbu-color-primary;
    //$wbu-title-color: #3d3c3c;
    $space_bottom: $wbu-margin * 5;
    $space_top: $wbu-margin * 4;
    $space_inner_top: $space_top * 0.5;
    $wbu-h4-font-size: $wbu-h4-font-size * 1.2;    
';
    $string .= $confs['files'];
    $filename = $this->themeName . '.scss';
    $path = $this->themePath . '/' . $this->themeName . '/wbu-atomique-theme/src/scss';
    debugLog::$debug = false;
    debugLog::logger($string, $filename, false, 'file', $path, true);
  }
  
  /**
   * --
   */
  function cssFiles() {
    $parser = new Compiler();
    $filenameScss = $this->themeName . '.scss';
    $path = $this->themePath . '/' . $this->themeName . '/scss/';
    $result = $parser->compile('@import "' . $path . $filenameScss . '";');
    $pathCss = $this->themePath . '/' . $this->themeName . '/css';
    debugLog::$debug = false;
    debugLog::logger($result, 'style-auto.css', false, 'file', $pathCss, true);
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
      'result' => $result
    ];
    debugLog::$debug = false;
    debugLog::kintDebugDrupal($debug, $name);
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