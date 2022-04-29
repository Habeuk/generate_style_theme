<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\PluginWithFormsInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Layout\LayoutInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Class LayoutEnteteForm.
 */
class LayoutEnteteForm extends FormBase {
  protected static $LayoutBaseKey = 'block_content.layout_entete_m1.default';
  protected static $LayoutSettingKey = 'third_party_settings.layout_builder.sections.0.layout_settings';
  protected static $plugin_id = 'formatage_models_header1';
  
  /**
   * The layout manager.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManagerInterface
   */
  protected $layoutManager;
  
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
    return 'layout_entete_form';
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->layoutManager = $container->get('plugin.manager.core.layout');
    $instance->sectionStorageManager = $container->get('plugin.manager.layout_builder.section_storage');
    return $instance;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $entity = \Drupal::entityTypeManager()->getStorage('entity_view_display')->load(self::$LayoutBaseKey);
    $contexts = [];
    $contexts['display'] = EntityContext::fromEntity($entity);
    $this->sectionStorage = $this->sectionStorageManager->load('defaults', $contexts);
    $this->layout = $this->sectionStorage->getSection(0)->getLayout();
    //
    $form['#tree'] = TRUE;
    $form['layout_settings'] = [];
    /**
     *
     * @var \Drupal\formatage_models\Plugin\Layout\Sections\Headers\FormatageModelsheader1 $plugin
     */
    $plugin = $this->getPluginForm($this->layout);
    
    // $plugin->setConfiguration($this->getLayoutCurrentConfig());
    $plugin->setConfiguration(NestedArray::mergeDeep($plugin->defaultConfiguration(), $plugin->getConfiguration()));
    // dump($plugin->getConfiguration());
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
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $subform_state = SubformState::createForSubform($form['layout_settings'], $form, $form_state);
    $this->getPluginForm($this->layout)->submitConfigurationForm($form['layout_settings'], $subform_state);
    //
    $configuration = $this->layout->getConfiguration();
    
    $this->sectionStorage->getSection(0)->setLayoutSettings($configuration);
    $this->sectionStorage->save();
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
