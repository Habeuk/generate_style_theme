<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Stephane888\Debug\debugLog;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\layout_builder\Section;
use Drupal\Core\Layout\LayoutInterface;
use Drupal\Core\Plugin\PluginWithFormsInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Class LayoutEnteteForm.
 * /layout_builder/configure/section/defaults/block_content.layout_entete_m1.default/1/formatage_models_header1
 * //
 * /layout_builder/configure/section/{defaults}/{block_content.layout_entete_m1.default}/{1}/{formatage_models_header1}
 * /layout_builder/configure/section/defaults/block_content.layout_entete_m1.default/0
 * /layout_builder/configure/section/{section_storage_type}/{section_storage}/{delta}/{plugin_id}
 * // **
 * section_storage_type = defaults
 * section_storage = block_content.layout_entete_m1.default
 * delta = 1
 * plugin_id = formatage_models_header1
 */
class LayoutEnteteFormV2 extends FormBase {
  protected static $LayoutBaseKey = 'core.entity_view_display.block_content.layout_entete_m1.default';
  protected static $LayoutSettingKey = 'third_party_settings.layout_builder.sections.0.layout_settings';
  protected static $plugin_id = 'formatage_models_header1';
  
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
  
  /**
   * The section storage manager.
   *
   * @var \Drupal\layout_builder\SectionStorage\SectionStorageManager
   */
  protected $sectionStorageManager;
  
  /**
   * The section storage.
   *
   * @var \Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage
   */
  protected $sectionStorage;
  
  /**
   * The plugin being configured.
   *
   * @var \Drupal\Core\Layout\LayoutInterface|\Drupal\Core\Plugin\PluginFormInterface
   */
  protected $layout;
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_entete_form_v2';
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->layoutManager = $container->get('plugin.manager.core.layout');
    $instance->ConfigFactory = $container->get('config.factory');
    $instance->sectionStorageManager = $container->get('plugin.manager.layout_builder.section_storage');
    return $instance;
  }
  
  public function buildFormN(array $form, FormStateInterface $form_state) {
    // Chargement de section storage:
    // dump($this->sectionStorageManager->load('defaults'));
    /**
     *
     * @var \Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay $data
     */
    $entity = \Drupal::entityTypeManager()->getStorage('entity_view_display')->load('block_content.layout_entete_m1.default');
    $contexts = [];
    $contexts['display'] = EntityContext::fromEntity($entity);
    $this->sectionStorage = $this->sectionStorageManager->load('defaults', $contexts);
    
    //
    // On charge les données ajoutées par le module layout_builder
    $layout_builder_data = $entity->getThirdPartySettings('layout_builder');
    // On recupere la section. ( la section represente, les données sauvegardées.
    if (!empty($layout_builder_data['sections'][0])) {
      /**
       *
       * @var \Drupal\layout_builder\Section $section
       */
      $section = $layout_builder_data['sections'][0];
      dump($section->toArray());
      dump($this->getLayoutCurrentConfig());
    }
  }
  
  /**
   * Permet de faire le rendu du layout.
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Form\FormInterface::buildForm()
   */
  public function buildForm2(array $form, FormStateInterface $form_state) {
    // chargement de section storage:
    // dump($this->sectionStorageManager->load('defaults'));
    /**
     *
     * @var \Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay $data
     */
    $entity = \Drupal::entityTypeManager()->getStorage('entity_view_display')->load('block_content.layout_entete_m1.default');
    $contexts = [];
    $contexts['display'] = EntityContext::fromEntity($entity);
    $this->sectionStorage = $this->sectionStorageManager->load('defaults', $contexts);
    
    //
    // On charge les données ajoutées par le module layout_builder
    $layout_builder_data = $entity->getThirdPartySettings('layout_builder');
    // On recupere la section. ( la section represente, les données sauvegardées.
    if (!empty($layout_builder_data['sections'][0])) {
      /**
       *
       * @var \Drupal\layout_builder\Section $section
       */
      $section = $layout_builder_data['sections'][0];
      return $section->toRenderArray();
    }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm1(array $form, FormStateInterface $form_state) {
    // chargement de section storage:
    // dump($this->sectionStorageManager->load('defaults'));
    /**
     *
     * @var \Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay $data
     */
    $entity = \Drupal::entityTypeManager()->getStorage('entity_view_display')->load('block_content.layout_entete_m1.default');
    $contexts = [];
    $contexts['display'] = EntityContext::fromEntity($entity);
    $this->sectionStorage = $this->sectionStorageManager->load('defaults', $contexts);
    
    //
    // On charge les données ajoutées par le module layout_builder
    $layout_builder_data = $entity->getThirdPartySettings('layout_builder');
    // On recupere la section. ( la section represente, les données sauvegardées.
    if (!empty($layout_builder_data['sections'][0])) {
      /**
       *
       * @var \Drupal\layout_builder\Section $section
       */
      $section = $layout_builder_data['sections'][0];
      dump($section->toRenderArray());
    }
    //
    // dump($data->getThirdPartySettings('layout_builder')); // layout_builder
    
    // debugLog::kintDebugDrupal($data, 'block_content.layout_entete_m1.default', true);
    // $LayoutConfig = \Drupal::config(self::$LayoutBaseKey);
    // dump($LayoutConfig->getRawData());
    /**
     *
     * @var \Drupal\formatage_models\Plugin\Layout\Sections\Headers\FormatageModelsheader1 $pluginHeader
     */
    $pluginHeader = $this->layoutManager->createInstance(self::$plugin_id);
    // $this->layoutManager->
    // $pluginHeader->setContext($name, $context);
    // v2lesroisdelareno_kksa
    $currentSetting = $this->getLayoutCurrentConfig();
    
    // dump($currentSetting);
    $pluginHeader->setConfiguration($currentSetting);
    // dump($currentSetting['v2lesroisdelareno_kksa']);
    $form += $pluginHeader->buildConfigurationForm($form, $form_state);
    // on supprime la marge-bottom
    if (empty($form['css_class']['css']['#default_value'])) {
      $form['css_class']['css']['#default_value'] = 'mb-0';
    }
    
    $form['submit'] = [
      '#type' => 'submit',
      '#attributes' => [
        'class' => [
          'mt-5'
        ]
      ],
      '#value' => $this->t('Save')
    ];
    return $form;
  }
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Form\FormInterface::buildForm()
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $entity = \Drupal::entityTypeManager()->getStorage('entity_view_display')->load('block_content.layout_entete_m1.default');
    $contexts = [];
    $contexts['display'] = EntityContext::fromEntity($entity);
    $this->sectionStorage = $this->sectionStorageManager->load('defaults', $contexts);
    /**
     *
     * @var \Drupal\layout_builder\Section $section
     */
    $section = $this->sectionStorage->getSection(0);
    // dump($section->toArray());
    //
    $this->layout = $section->getLayout();
    $form['#tree'] = TRUE;
    $form['layout_settings'] = [];
    /**
     *
     * @var \Drupal\formatage_models\Plugin\Layout\Sections\Headers\FormatageModelsheader1 $plugin
     */
    $plugin = $this->getPluginForm($this->layout);
    // dump($plugin->getConfiguration());
    // $plugin->setConfiguration($this->getLayoutCurrentConfig());
    $form['layout_settings'] = $plugin->buildConfigurationForm($form['layout_settings'], $form_state);
    
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'save',
      '#button_type' => 'primary'
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
    // dump($currentSetting);
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
    return \Drupal\wbumenudomain\Wbumenudomain::getCurrentdomain();
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
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // On applique la sousmission à partir de la function submit du layout.
    $subform_state = SubformState::createForSubform($form['layout_settings'], $form, $form_state);
    $this->getPluginForm($this->layout)->submitConfigurationForm($form['layout_settings'], $subform_state);
    //
    $plugin_id = $this->layout->getPluginId();
    $configuration = $this->layout->getConfiguration();
    dump([
      'BEFORE save' => $configuration
    ]);
    // die();
    
    // if ($this->isUpdate) {
    // $this->sectionStorage->getSection($this->delta)->setLayoutSettings($configuration);
    // }
    // else {
    // $this->sectionStorage->insertSection($this->delta, new Section($plugin_id, $configuration));
    // }
    $this->sectionStorage->getSection(0)->setLayoutSettings($configuration);
    $this->sectionStorage->save();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm0(array &$form, FormStateInterface $form_state) {
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
  
  /**
   * Retrieves the plugin form for a given layout.
   *
   * @param \Drupal\Core\Layout\LayoutInterface $layout
   *        The layout plugin.
   *        
   * @return \Drupal\Core\Plugin\PluginFormInterface The plugin form for the layout.
   */
  protected function getPluginForm(LayoutInterface $layout) {
    if ($layout instanceof PluginWithFormsInterface) {
      return $this->pluginFormFactory->createInstance($layout, 'configure');
    }
    
    if ($layout instanceof PluginFormInterface) {
      return $layout;
    }
    throw new \InvalidArgumentException(sprintf('The "%s" layout does not provide a configuration form', $layout->getPluginId()));
  }
  
}
