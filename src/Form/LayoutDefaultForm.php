<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Stephane888\Debug\debugLog;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\Core\Plugin\Context\EntityContext;

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  
  /**
   * The entity type manager.
   *
   * @var \Drupal\layout_builder\SectionStorage\SectionStorageManager
   */
  protected $LayoutBuilderSectionStorage;
  
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
    $instance->LayoutBuilderSectionStorage = $container->get('plugin.manager.layout_builder.section_storage');
    $instance->layoutBuilderSampleEntityGenerator = $container->get('layout_builder.sample_entity_generator');
    $instance->layoutManager = $container->get('plugin.manager.core.layout');
    $instance->ConfigFactory = $container->get('config.factory');
    $instance->entityTypeManager = $container->get('entity_type.manager');
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
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $plugin_id = NULL) {
    $plugin_id = 'formatage_models_header1';
    $entity_type_id = 'entity_view_display';
    $entity_storage_id = 'block_content.layout_entete_m1.full';
    /**
     *
     * @var \Drupal\Core\Form\FormBuilderInterface $builder
     */
    // $builder = \Drupal::formBuilder();
    // $form = $builder->getForm('\Drupal\layout_builder\Form\ConfigureSectionForm', $section_storage, $delta, $plugin_id);
    
    /**
     * On charge un pluginId
     *
     * @var \Drupal\formatage_models\Plugin\Layout\Sections\Headers\FormatageModelsheader1 $pluginHeader
     */
    $pluginHeader = $this->layoutManager->createInstance($plugin_id);
    //
    /**
     * On initialise l'object qui gere les sauvegardes.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface $storage
     */
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    /**
     * ici, on charge la donnée.
     * (il faut penser à la creer si elle n'existe pas ).
     * Ce bloc fait un chargement par defaut des données.
     *
     * @var Ambigous <\Drupal\Core\Entity\EntityInterface, NULL> $display
     */
    $entityViewDisplay = $storage->load($entity_storage_id);
    // on recupere le contexte. ( à quoi correspond contexte, ou il est definit au niveau de layout buider )???
    $contexts = [];
    $contexts['display'] = EntityContext::fromEntity($entityViewDisplay);
    
    // Ici, on charge le model de stokage definit par layout. ce dernier à besoin d'un contexte d'affichage.
    /**
     *
     * @var \Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage $LayoutBuilderSectionStorage
     */
    $LayoutBuilderSectionStorage = $this->LayoutBuilderSectionStorage->load('defaults', $contexts);
    // dump($LayoutBuilderSectionStorage->get);
    $pluginHeader->setContext('display', $contexts['display']);
    // $pluginHeader->
    // v2lesroisdelareno_kksa
    // $currentSetting = $this->getLayoutCurrentConfig();
    // dump($currentSetting);
    // $pluginHeader->setConfiguration($currentSetting);
    // dump($currentSetting['v2lesroisdelareno_kksa']);
    $form['#tree'] = TRUE;
    $form['layout_settings'] = [];
    $form['layout_settings'] = $pluginHeader->buildConfigurationForm($form, $form_state);
    
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
