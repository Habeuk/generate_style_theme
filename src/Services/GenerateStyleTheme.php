<?php

namespace Drupal\generate_style_theme\Services;

use Drupal\Core\Controller\ControllerBase;
use Drupal\generate_style_theme\Services\Reposotories\GenerateFiles;
use Drupal\generate_style_theme\Entity\ConfigThemeEntity;
use Drupal\Component\Serialization\Json;
use Drupal\generate_style_theme\GenerateStyleTheme as GenerateStyleThemeConfig;
use Drupal\Core\File\FileSystem;

class GenerateStyleTheme extends ControllerBase {
  protected $themeName;
  protected $themeDirectory;
  protected $themePath;
  protected $entity;

  /**
   * Nom du theme parent.
   *
   * @var string
   */
  protected $baseTheme;
  /**
   * Contient la configuation du module generate_style_theme.
   */
  protected $generate_style_themeSettings = [];

  /**
   * Contient la cle du theme qui est encours de traitement.
   * example : domain.config.arche5_lesroisdelareno_fr.system.theme si on
   * utilise le module domain.
   * Sinon,
   * system.theme
   */
  protected $configKeyTheme = null;
  /**
   * Contient la cle des informations du site qui est encours de traitement.
   * example : domain.config.arche5_lesroisdelareno_fr.system.site si on utilise
   * le module domain.
   * Sinon,
   * system.site
   */
  protected $configKeySite = null;

  /**
   * Contient la clée des paramettres du theme qui est encours de traitement.
   * example :
   * domain.config.arche5_lesroisdelareno_fr.arche5_lesroisdelareno_fr.settings
   * si on utilise le module domain.
   * Sinon,
   * arche5_lesroisdelareno_fr.settings
   */
  protected $configKeyThemeSettings = null;
  /**
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory = null;

  /**
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;
  protected $hasError = false;

  /**
   *
   * @var FileSystem
   */
  protected $FileSystem;
  
  /**
   *
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $pathResolver;

  use GenerateFiles;

  /**
   *
   * @param Array $configs
   * @param ConfigThemeEntity $entity
   */
  function __construct(ConfigThemeEntity $entity) {
    $this->themeName = $entity->getHostname();
    // $this->themeDirectory = $entity->getHostname();
    $this->themePath = $this->getPath();
    $this->entity = $entity;
    $this->generate_style_themeSettings = $this->getConfiguration();
    $this->baseTheme = $this->generate_style_themeSettings['tab1']['theme_base'];
    $this->configFactory = \Drupal::service('config.factory');
    $this->logger = \Drupal::logger('generate_style_theme');
    $this->pathResolver = \Drupal::service('extension.path.resolver');
    $this->setDynamicConfig();
  }

  private function getConfiguration() {
    $config = \Drupal::config('generate_style_theme.settings')->getRawData();
    $this->ValidConfig($config);
    return $config;
  }

  /**
   * Definie une configuration variable.
   */
  private function setDynamicConfig() {
    $conf = GenerateStyleThemeConfig::getDynamicConfig($this->themeName, $this->generate_style_themeSettings);
    $this->configKeyTheme = $conf['theme'];
    $this->configKeyThemeSettings = $conf['settings'];
    $this->configKeySite = $conf['site'];
    $this->FileSystem = \Drupal::getContainer()->get('file_system');
    // if ($this->generate_style_themeSettings['tab1']['use_domain']) {
    // if (\Drupal::moduleHandler()->moduleExists('domain')) {
    // $this->configKeyTheme = 'domain.config.' . $this->themeName .
    // '.system.theme';
    // $this->configKeyThemeSettings = 'domain.config.' . $this->themeName . '.'
    // . $this->themeName . '.settings';
    // }
    // }
  }

  /**
   * \Drupal::config('generate_style_theme.settings')->getRawData();
   * Return le chemin vers le dossier parent du theme definit par defaut.
   * ( Logiquement il devrait pointer sur custom, pour que tous les themes soit
   * disponible dans custom ).
   *
   * @throws \Exception
   * @return string
   */
  protected function getPath() {
    $defaultThemeName = \Drupal::config('system.theme')->get('default');
    $path_of_module = DRUPAL_ROOT . '/' . $this->pathResolver->getPath('theme', $defaultThemeName);
    $path_of_module = explode("/", $path_of_module);
    //
    if (!empty($path_of_module)) {
      $path = '';
      for ($i = 0; $i < count($path_of_module) - 1; $i++) {
        if ($path_of_module[$i])
          $path .= "/" . $path_of_module[$i];
      }
      // On corrige le chemain s'il n'est pas valide.
      if (str_contains($path, "/core/themes")) {
        $path = str_replace("/core/themes", "/themes/custom", $path);
      }
      return $path;
    }
    throw new \Exception(" Impossible de determiner le chemin vers le dossier du theme. ");
  }

  /**
   *
   * @param Boolean $createThme
   */
  function buildSubTheme($createThme = false, $run_npm = false) {
    try {
      $this->InfoYml();
      $this->LibrairiesYml();
      if ($this->entity->get('force_regenerate_npm_files')->value) {
        $this->DeleteFilesNpm();
        $createThme = true;
      }

      if ($createThme)
        $this->CopyWbuAtomiqueTheme();

      $this->scssFiles();
      $this->jsFiles();
      //
      if ($run_npm)
        $this->RunNpm();
      elseif ($this->entity->get('run_npm')->value)
        $this->RunNpm();
      $this->SetCurrentThemeDefaultOfDomaine();
      $this->setConfigTheme();
      $this->setLogoToTheme();
      // $this->entity->validate();
    } catch (\Exception $e) {
      $this->logger->warning($e->getMessage());
    }
  }

  /**
   * --
   */
  function deleteSubTheme() {
    if (!empty($this->themePath) && !empty($this->themeName)) {
      $path = $this->themePath . '/' . $this->themeName;
      $script = " sudo rm -rf " . $path;
      $this->excuteCmd($script);
    }
  }

  /**
   * --
   */
  protected function setConfigTheme() {
    $site_config = $this->entity->getsite_config();
    if (!empty($site_config)) {
      $siteConfValue = Json::decode($site_config);
      if (!empty($siteConfValue['page.front'])) {
        $editConfig = $this->configFactory->getEditable($this->configKeySite);
        $editConfig->set('page.front', $siteConfValue['page.front']);
        $editConfig->set('page.403', $siteConfValue['page.403']);
        $editConfig->set('page.404', $siteConfValue['page.404']);
        $editConfig->set('name', $siteConfValue['name']);
        // si un utilisateur n'est pas explicement definit, on utilise celui de
        // l'utilisateur connecter.
        // (NB: cette approche n'est ok, car elle permet pas d'envoyer des
        // mails.
        // Pour envoyer des mails de manieres assez sûr l'email doit etre en
        // function du domaine).
        if (empty($siteConfValue['mail'])) {
          $editConfig->set('mail', \Drupal::currentUser()->getEmail());
        } else
          $editConfig->set('mail', $siteConfValue['mail']);
        //
        $editConfig->save();
      } else {
        \Drupal::messenger()->addWarning(' Imposible de mettre à jour la page home ');
      }
    }
  }

  protected function setLogoToTheme() {
    if ($this->configKeyThemeSettings) {
      /**
       *
       * @var \Drupal\Core\Config\ConfigFactoryInterface $editConfigTheme
       */
      $editConfigTheme = $this->configFactory->getEditable($this->configKeyThemeSettings);
      $pathLogo = $this->entity->getLogo();
      if (!empty($pathLogo)) {
        $editConfigTheme->set('logo.path', $pathLogo)->save();
        $editConfigTheme->set('logo.use_default', 0)->save();
      }
    }
  }

  /**
   * Permet de valider la configuration du module.
   *
   * @deprecated doit etre supprimer un foix le module fonctionne sur
   *             lesroisdelareno et le storibon.
   */
  protected function ValidConfig(array $config) {
    if (!empty($config['tab1']['theme_base']) && isset($config['tab1']['use_domain'])) {
      return true;
    }
    throw new \LogicException("Certaines données de configuration sont inexistantes.");
  }

  /**
   * Permet de definir le theme selectionner comme theme par defaut pour le
   * domaine choisie et applique quelques paramettre de configurations.
   * Cette configuration est etroitement lier au module domain.
   * Dans le module domaine, il ya déjà une logique de surchage de la
   * configuration qui est mis en place. voir sous module domain_config et
   * domain_config_ui
   * Les partterns suivant peuvent etre utiliser :
   * domain.config.DOMAIN_MACHINE_NAME.LANGCODE.item.name,
   * domain.config.DOMAIN_MACHINE_NAME.item.name
   * example : system.site => domain.config.v2lesroisdelareno_kksa.system.site (
   * pour le domaine v2lesroisdelareno_kksa ).
   */
  protected function SetCurrentThemeDefaultOfDomaine() {
    if ($this->themeName && $this->entity->SetThemeAsDefaut()) {

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
      if (empty($listThemes[$this->themeName])) {
        // \Drupal::messenger()->addStatus(' Theme installé : ' .
        // $this->themeName);
        /**
         *
         * @var \Drupal\generate_style_theme\Services\Themes\ActiveAsignService $ActiveAsignService
         */
        $ActiveAsignService = \Drupal::service('generate_style_theme.active_asign');
        $ActiveAsignService->ActiveThemeForDomaine([
          $this->themeName => $this->themeName
        ]);
      } else {
        // \Drupal::messenger()->addStatus(' Theme deja installé : ' .
        // $this->themeName);
      }

      $configs = \Drupal::config($this->configKeyTheme);
      $defaultThemeName = $configs->get('default');

      // On definit le theme comme theme par defaut pour le nouveau theme.
      if ($this->themeName != $defaultThemeName) {
        /**
         *
         * @var \Drupal\Core\Config\ConfigFactoryInterface $editConfig
         */
        $editConfig = $this->configFactory->getEditable($this->configKeyTheme);
        $editConfig->set('default', $this->themeName)->save();
        // \Drupal::messenger()->addStatus(' Theme definie par defaut : ' .
        // $this->configKeyTheme);
      }
    }
  }
}
