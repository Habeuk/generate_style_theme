<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Stephane888\Debug\debugLog;

/**
 * Class LayoutDefaultForm.
 */
class LayoutDefaultForm extends FormBase {
  
  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;
  
  /**
   * Drupal\layout_builder\LayoutTempstoreRepositoryInterface definition.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutBuilderTempstoreRepository;
  
  /**
   * Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface definition.
   *
   * @var \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface
   */
  protected $pluginManagerLayoutBuilderSectionStorage;
  
  /**
   * Drupal\layout_builder\Entity\SampleEntityGeneratorInterface definition.
   *
   * @var \Drupal\layout_builder\Entity\SampleEntityGeneratorInterface
   */
  protected $layoutBuilderSampleEntityGenerator;
  
  /**
   * The layout manager.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManagerInterface
   */
  protected $layoutManager;
  
  /**
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $ConfigFactory;
  protected static $LayoutBaseKey = 'core.entity_view_display.block_content.layout_entete_m1.default';
  protected static $LayoutSettingKey = 'third_party_settings.layout_builder.sections.0.layout_settings';
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->loggerFactory = $container->get('logger.factory');
    $instance->layoutBuilderTempstoreRepository = $container->get('layout_builder.tempstore_repository');
    $instance->pluginManagerLayoutBuilderSectionStorage = $container->get('plugin.manager.layout_builder.section_storage');
    $instance->layoutBuilderSampleEntityGenerator = $container->get('layout_builder.sample_entity_generator');
    $instance->layoutManager = $container->get('plugin.manager.core.layout');
    $instance->ConfigFactory = $container->get('config.factory');
    return $instance;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_default_form';
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $plugin_id = 'formatage_models_header1';
    /**
     *
     * @var \Drupal\formatage_models\Plugin\Layout\Sections\Headers\FormatageModelsheader1 $pluginHeader
     */
    $pluginHeader = $this->layoutManager->createInstance($plugin_id);
    
    // v2lesroisdelareno_kksa
    $currentSetting = $this->getLayoutCurrentConfig();
    // dump($currentSetting);
    $pluginHeader->setConfiguration($currentSetting);
    // dump($currentSetting['v2lesroisdelareno_kksa']);
    $form += $pluginHeader->buildConfigurationForm($form, $form_state);
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit')
    ];
    
    return $form;
  }
  
  /**
   *
   * @return array[]
   */
  protected function getLayoutCurrentConfig() {
    $LayoutConfig = $this->ConfigFactory->get(self::$LayoutBaseKey);
    $currentSetting = $LayoutConfig->get(self::$LayoutSettingKey);
    $DomaineId = $this->getDomaineId();
    if (!empty($currentSetting[$DomaineId])) {
      return $currentSetting[$DomaineId];
    }
    $this->removeAnotherDomainId($currentSetting);
    return $currentSetting;
  }
  
  /**
   *
   * @return string
   */
  protected function getDomaineId() {
    return 'v2lesroisdelareno_kksa';
  }
  
  /**
   * Permet de supprimer les autres enregistrement de domaine.
   *
   * @param array $Conf
   */
  protected function removeAnotherDomainId(array &$Conf) {
    $hostNames = \Drupal\wbumenudomain\Wbumenudomain::getAlldomaines();
    foreach ($hostNames as $k => $value) {
      if (!empty($Conf[$k]))
        unset($Conf[$k]);
    }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $defualtConfigs = $this->getLayoutCurrentConfig();
    $currentConfig = $form_state->getValues();
    $newConfig = [];
    foreach ($currentConfig as $k => $value) {
      if (isset($defualtConfigs[$k]))
        $newConfig[$k] = $value;
    }
    $LayoutConfig = $this->ConfigFactory->getEditable(self::$LayoutBaseKey);
    $DomaineId = $this->getDomaineId();
    $LayoutConfig->set(self::$LayoutSettingKey . '.' . $DomaineId, $newConfig)->save();
  }
  
}
