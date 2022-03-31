<?php

namespace Drupal\generate_style_theme\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ThemeInstaller;

/**
 * Returns responses for Generate style theme routes.
 */
class GenerateStyleThemeController extends ControllerBase {
  
  /**
   * Drupal\Core\Theme\ThemeManagerInterface definition.
   *
   * @var \Drupal\Core\Extension\ThemeInstaller
   */
  protected $themeInstaller;
  
  public function __construct(ThemeInstaller $themeInstaller) {
    $this->themeInstaller = $themeInstaller;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('theme_installer'));
  }
  
  /**
   * Builds the response.
   */
  public function build() {
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!')
    ];
    return $build;
  }
  
  /**
   * permet d'installer un theme.
   */
  public function installTheme($themename, $domaine_id) {
    /**
     *
     * @var \Drupal\Core\Extension\ThemeExtensionList $ExtLitThemes
     */
    $ExtLitThemes = \Drupal::service('extension.list.theme');
    $ExtLitThemes->reset();
    $listThemeVisible = $ExtLitThemes->getList();
    $debug = [
      $themename,
      $listThemeVisible
    ];

    if (!empty($listThemeVisible[$themename])) {
      $listThemesInstalled = $listThemesInstalled = \Drupal::config("core.extension")->get('theme');
      if (empty($listThemesInstalled[$themename])) {
        $theme_list = [
          $themename => $themename
        ];
        $this->themeInstaller->install($theme_list);
        $message = " Installation du theme : " . $themename;
        $this->messenger()->addMessage($message);
        //
        $baseConfig = 'system.theme';
        $key = 'domain.config.' . $domaine_id . '.' . $baseConfig;
        $configs = \Drupal::config($key);
        $defaultThemeName = $configs->get('default');
        // On definit le theme comme theme par defaut pour le nouveau theme.
        if ($domaine_id != $defaultThemeName) {
          /**
           *
           * @var \Drupal\Core\Config\ConfigFactoryInterface $editConfig
           */
          $editConfig = \Drupal::service('config.factory')->getEditable($key);
          $editConfig->set('default', $domaine_id)->save();
          \Drupal::messenger()->addStatus(' Theme definie par defaut : ' . $key);
        }
        /**
         *
         * @var \Drupal\domain\Entity\Domain $Domain
         */
        $Domain = \Drupal::entityTypeManager()->getStorage('domain')->load($domaine_id);
        $uri = $Domain->getScheme() . $Domain->getHostname();
        $options = [];
        $options['absolute'] = true;
        $options['external'] = true;
        $options['attributes']['target'] = 'blank';
        $build = [
          '#theme' => 'generate_style_theme_success',
          '#description' => "Le theme a été cree. <br> Vous pouvez acceder à dernier via le lien ci-dessous",
          '#title' => 'Les fichiers de votre theme ont eté creer avec success ',
          '#description2' => "Le theme a été cree mais il ne dispose pas de contenu. <br> Vous pouvez passez à l'etape suivante ",
          '#title' => 'Les fichiers de votre theme ont eté creer avec success ',
          'etape_suivante2' => [
            '#type' => 'link',
            '#title' => 'Etape suivante : Ajouter du contenu',
            '#url' => \Drupal\Core\Url::fromRoute('generate_style_theme.create_pages_site_form')
          ],
          'etape_suivante' => [
            '#type' => 'link',
            '#title' => 'voir le theme',
            '#url' => \Drupal\Core\Url::fromUri($uri, $options)
          ]
        ];
        return $build;
      }
      else {
        $message = " Le theme est deja installé : " . $themename;
        $this->messenger()->addMessage($message);
      }
    }
    else {
      $message = " Le theme n'est pas encore vue par drupal : " . $themename;
      $this->messenger()->addWarning($message);
    }
    //
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Theme => ' . $themename)
    ];
    return $build;
  }
  
}