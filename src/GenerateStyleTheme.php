<?php

namespace Drupal\generate_style_theme;

class GenerateStyleTheme {
  
  /**
   * Returns the theme hook definition information.
   */
  public static function getThemeHooks() {
    $hooks['generate_style_theme_success'] = [
      'render element' => 'element'
    ];
    return $hooks;
  }
  
  /**
   * Permet de charger les libraries renvoyant vers un theme.
   * Cette approche est utilisé par le modules wbumenudomain.
   */
  public static function getLibrairiesCurrentTheme() {
    // $config = \Drupal::config('generate_style_theme.settings')->getRawData();
    $libraries = [];
    if (\Drupal::moduleHandler()->moduleExists('wbumenudomain')) {
      $libraries = \Drupal\wbumenudomain\Wbumenudomain::getLibrairiesCurrentTheme();
    }
    return $libraries;
  }
  
  /**
   *
   * @param string $value
   * @param string $entityTypeId
   */
  public static function getThemes($value, $entityTypeId) {
    $config = \Drupal::config('generate_style_theme.settings')->getRawData();
    // dump($config);
    if ($config['tab1']['use_domain']) {
      if (\Drupal::moduleHandler()->moduleExists('domain')) {
        return GenerateStyleThemeDomain::getUnUseDomain($value, $entityTypeId);
      }
      else {
        \Drupal::messenger()->addWarning(' Vous devez installer le module domain_content ');
      }
    }
    else {
      // lors de la mofication on renvoit la valeur.
      if (!empty($value)) {
        return [
          $value => $value
        ];
      }
      // Dans ce cas on prevoit uniquement le theme avec le nom du domaine.
      $siteName = \Drupal::config('system.site')->get('name');
      return [
        $siteName => 'Nouveau theme'
      ];
    }
  }
  
  /**
   * Permet de recuperer les clées de configuration en function des
   * envirronements.
   *
   * @param string $themeName
   * @param array $ModuleConf
   *        Configuration du module generate_style_theme.
   * @return string[]
   */
  public static function getDynamicConfig($themeName, array $ModuleConf = []) {
    $config = [
      'theme' => 'system.theme',
      'settings' => $themeName . '.settings',
      'site' => 'system.site'
    ];
    // Override value if necessary.
    if (!empty($ModuleConf['tab1']['use_domain'])) {
      if (\Drupal::moduleHandler()->moduleExists('domain')) {
        $config = [
          'theme' => 'domain.config.' . $themeName . '.system.theme',
          'settings' => 'domain.config.' . $themeName . '.' . $themeName . '.settings',
          'site' => 'domain.config.' . $themeName . '.system.site'
        ];
      }
    }
    return $config;
  }
  
}