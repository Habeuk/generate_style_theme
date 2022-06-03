<?php

namespace Drupal\generate_style_theme\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ThemeInstaller;
use Drupal\generate_style_theme\Services\GenerateStyleTheme;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\generate_style_theme\Entity\ConfigThemeEntity;

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
   * --
   */
  public function UpdateStyleTheme($hostname) {
    $entities = $this->entityTypeManager()->getStorage('config_theme_entity')->loadByProperties([
      'hostname' => $hostname
    ]);
    if (!empty($entities)) {
      /**
       *
       * @var ConfigThemeEntity $entity
       */
      $entity = reset($entities);
      $GenerateStyleTheme = new GenerateStyleTheme($entity);
      $GenerateStyleTheme->buildSubTheme(false, true);
      return $this->reponse($entity->toArray());
    }
    return $this->reponse('', 400, 'hostname non trouvée : ' . $hostname);
  }
  
  /**
   * Permet d'installer un theme.
   */
  public function installTheme($themename, $domaine_id = null) {
    /**
     *
     * @var \Drupal\Core\Extension\ThemeExtensionList $ExtLitThemes
     */
    $ExtLitThemes = \Drupal::service('extension.list.theme');
    $ExtLitThemes->reset();
    $listThemeVisible = $ExtLitThemes->getList();
    
    if (!empty($listThemeVisible[$themename])) {
      // $config = $this->config('generate_style_theme.settings')->getRawData();
      $listThemesInstalled = $listThemesInstalled = \Drupal::config("core.extension")->get('theme');
      if (empty($listThemesInstalled[$themename])) {
        $theme_list = [
          $themename => $themename
        ];
        $this->themeInstaller->install($theme_list);
        $message = " Installation du theme : " . $themename;
        $this->messenger()->addMessage($message);
        
        $options = [];
        $options['absolute'] = true;
        $options['external'] = true;
        $options['attributes']['target'] = 'blank';
        $build = [
          '#theme' => 'generate_style_theme_success',
          '#description' => "Le theme a été cree. <br> Vous pouvez acceder à dernier via le lien ci-dessous",
          '#title' => 'Les fichiers de votre theme ont eté creer avec success '
          // 'etape_suivante2' => [
          // '#type' => 'link',
          // '#title' => 'Etape suivante : Ajouter du contenu',
          // '#url' =>
          // \Drupal\Core\Url::fromRoute('generate_style_theme.create_pages_site_form')
          // ]
        ];
        if ($domaine_id && \Drupal::moduleHandler()->moduleExists('domain')) {
          /**
           *
           * @var \Drupal\domain\Entity\Domain $Domain
           */
          $Domain = \Drupal::entityTypeManager()->getStorage('domain')->load($domaine_id);
          if ($Domain) {
            $uri = $Domain->getScheme() . $Domain->getHostname();
            $build['etape_suivante'] = [
              '#type' => 'link',
              '#title' => 'voir le theme',
              '#url' => \Drupal\Core\Url::fromUri($uri, $options)
            ];
          }
        }
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
  
  /**
   *
   * @param array|string $configs
   * @param number $code
   * @param string $message
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected function reponse($configs, $code = null, $message = null) {
    if (!is_string($configs))
      $configs = Json::encode($configs);
    $reponse = new JsonResponse();
    if ($code)
      $reponse->setStatusCode($code, $message);
    $reponse->setContent($configs);
    return $reponse;
  }
  
}