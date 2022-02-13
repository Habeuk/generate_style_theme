<?php

namespace Drupal\generate_style_theme\Services\Themes;

use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;

/**
 * Class ActiveAsignService.
 */
class ActiveAsignService {
  
  /**
   * Drupal\Core\Theme\ThemeManagerInterface definition.
   *
   * @var \Drupal\Core\Extension\ThemeInstaller
   */
  protected $themeInstaller;
  
  /**
   * Construcs a new ActiveAsignService object.
   */
  public function __construct(ThemeInstallerInterface $themeInstaller) {
    $this->themeInstaller = $themeInstaller;
  }
  
  /**
   * Active un theme pour un domaine.
   */
  public function ActiveThemeForDomaine(Array $theme_list) {
    $this->themeInstaller->install($theme_list);
  }
  
}
