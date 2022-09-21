<?php

namespace Drupal\generate_style_theme\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\WidgetBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Stephane888\HtmlBootstrap\ThemeUtility;
use Drupal\Component\Serialization\Json;
use Drupal\generate_style_theme\GenerateStyleTheme as GenerateStyleThemeConfig;

/**
 *
 * @deprecated ce champs ne peut etre utiliser par plusieurs entité ( actuelment
 *             c'est : config_theme_entity )
 *             A widget bar.
 *
 * @FieldWidget(
 *   id = "wbumenudomainsiteconfig",
 *   label = @Translation(" Wbumenudomain Widget Site Config "),
 *   field_types = {
 *     "wbumenudomaineditlink",
 *   },
 *   multiple_values = TRUE
 * )
 */
class WbumenudomainSiteconfig extends WidgetBase {
  protected $ThemeUtility;

  /**
   * Constructs a WidgetBase object.
   *
   * @param string $plugin_id
   *        The plugin_id for the widget.
   * @param mixed $plugin_definition
   *        The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *        The definition of the field to which the widget is associated.
   * @param array $settings
   *        The widget settings.
   * @param array $third_party_settings
   *        Any third party settings.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ThemeUtility $ThemeUtility) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->ThemeUtility = $ThemeUtility;
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['third_party_settings'], $container->get('generate_style_theme.themeutility'));
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'placeholder' => ''
    ] + parent::defaultSettings();
  }

  /**
   *
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // get value
    // dump($items[$delta]->value);
    // $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    // on recupere le domaine-id à partir du champs (via l'action ajax).
    $hostname = $form_state->getValue('hostname');
    if (!empty($hostname[0]['value'])) {
      $hostname = $hostname[0]['value'];
      $this->ThemeUtility->ActiveUseAjax();
    }
    // Si non, on recupere à partir de l'entité.( pour l'edition).
    elseif (\Drupal::routeMatch()->getRouteName() == 'entity.config_theme_entity.edit_form') {
      // dump(\Drupal::routeMatch()->getParameters());
      /**
       *
       * @var \Drupal\generate_style_theme\Entity\ConfigThemeEntity $config_theme_entity
       */
      if ($config_theme_entity = \Drupal::routeMatch()->getParameter('config_theme_entity')) {
        $hostname = $config_theme_entity->getHostname();
      }
    }
    // Si non, on recupere à partir de l'url.
    else {
      $request = \Drupal::request();
      if ($request->query->has('domaine-id')) {
        $hostname = $request->query->get('domaine-id');
      }
    }

    $element['siteconf'] = [];
    $this->ThemeUtility->addContainerTree('container', $element['siteconf'], 'Configuration du site', true);
    $element['siteconf']['container']['#prefix'] = '<div id="wbumenudomain-siteconfig">';
    $element['siteconf']['container']['#suffix'] = '</div>';
    // la MAJ ne se fait plus au niveau de la validation
    // $element['siteconf']['container']['#element_validate'][] = [
    // $this,
    // 'validateElement'
    // ];
    // On recupere les données de la configuration system.site. Pour toujours
    // avoir la bonne valeur meme si ce dernier a été maj par un autre module.
    /**
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory
     */
    $configFactory = \Drupal::service('config.factory');

    if (!empty($hostname)) {
      $config = \Drupal::config('generate_style_theme.settings')->getRawData();
      $conf = GenerateStyleThemeConfig::getDynamicConfig($hostname, $config);
      $siteConf = \Drupal::config($conf['site'])->getRawData();
    }
    else {
      $config = \Drupal::config('system.site')->getRawData();
      $siteConf = $config;
    }
    $this->formSiteConfig($element['siteconf']['container'], $siteConf);

    // die();
    // $form_state->setRebuild();
    return $element;
  }

  /**
   *
   * @param array $form
   * @param int $id_config
   * @param array $siteConf
   */
  public function formSiteConfig(array &$form, array $siteConf = []) {
    // edit config
    // $this->ThemeUtility->addHiddenTree('edit-config', $form, $id_config);
    // name
    $name = isset($siteConf['name']) ? $siteConf['name'] : '';
    $this->ThemeUtility->addTextfieldTree('name', $form, 'Nom du site', $name);
    // slogan
    $slogan = isset($siteConf['slogan']) ? $siteConf['slogan'] : '';
    $this->ThemeUtility->addTextfieldTree('slogan', $form, 'Slogan', $slogan);
    // mail
    $mail = isset($siteConf['mail']) ? $siteConf['mail'] : '';
    $this->ThemeUtility->addTextfieldTree('mail', $form, 'Adresse de courriel (administrateur)', $mail);
    // page.front
    $page_front = isset($siteConf['page']["front"]) ? $siteConf['page']["front"] : '';
    $this->ThemeUtility->addTextfieldTree('page.front', $form, "Page d'accueil par défaut", $page_front);
    // page.403
    $page_403 = isset($siteConf['page']["403"]) ? $siteConf['page']["403"] : '';
    $this->ThemeUtility->addTextfieldTree('page.403', $form, "Page 403 par défaut (accès refusé)", $page_403);
    // page.404
    $page_404 = isset($siteConf["page"]["404"]) ? $siteConf["page"]["404"] : '';
    $this->ThemeUtility->addTextfieldTree('page.404', $form, "Page 404 par défaut ( page non trouvée )", $page_404);
  }

  /**
   *
   * @param array $element
   * @param FormStateInterface $form_state
   */
  // public function validateElement(array $element, FormStateInterface
  // $form_state) {
  // // die();
  // $vals = $form_state->getUserInput();

  // // name
  // if (!empty($vals['site_config'][0]['siteconf']['container'])) {
  // $siteConfValue = $vals['site_config'][0]['siteconf']['container'];
  // // idConf
  // $this->WbumenudomainConf->SetIdConfig($siteConfValue['edit-config']);
  // // name
  // $this->WbumenudomainConf->SaveValue('name', $siteConfValue['name']);
  // // slogan
  // $this->WbumenudomainConf->SaveValue('slogan', $siteConfValue['slogan']);
  // // mail
  // $this->WbumenudomainConf->SaveValue('mail', $siteConfValue['mail']);
  // // page.front
  // $this->WbumenudomainConf->SaveValue('page.front',
  // $siteConfValue['page.front']);
  // // page.403
  // $this->WbumenudomainConf->SaveValue('page.403',
  // $siteConfValue['page.403']);
  // // page.404
  // $this->WbumenudomainConf->SaveValue('page.404',
  // $siteConfValue['page.404']);
  // }
  // }

  /**
   *
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => t(' Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format. ')
    ];
    return $element;
  }

  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Field\WidgetBase::massageFormValues()
   */
  public function massageFormValues($values, $form, $form_state) {
    if (!empty($values['siteconf']['container'])) {
      return [
        'value' => Json::encode($values['siteconf']['container'])
      ];
    }
    elseif (!empty($values['page.front'])) {
      return [
        'value' => Json::encode($values)
      ];
    }
    return parent::massageFormValues($values, $form, $form_state);
  }

}