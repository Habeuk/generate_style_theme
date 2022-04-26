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
use Drupal\Component\Utility\NestedArray;

class LayoutMergeConfigForm extends FormBase {
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
    return 'layout_restaure_config';
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
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Form\FormInterface::buildForm()
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $params = \Drupal::routeMatch()->getParameters()->all();
    $section_storage = 'block_content.layout_entete_m1.default';
    if (isset($params['section_storage'])) {
      $section_storage = $params['section_storage'];
    }
    $entity = \Drupal::entityTypeManager()->getStorage('entity_view_display')->load($section_storage);
    $contexts = [];
    $contexts['display'] = EntityContext::fromEntity($entity);
    $this->sectionStorage = $this->sectionStorageManager->load('defaults', $contexts);
    /**
     *
     * @var Section $section
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
    
    $plugin->setConfiguration(NestedArray::mergeDeep($plugin->defaultConfiguration(), $plugin->getConfiguration()));
    // dump($plugin->getConfiguration(), $plugin->getSubConfiguration(), NestedArray::mergeDeep($plugin->defaultConfiguration(), $plugin->getConfiguration()));
    // $plugin->setConfiguration($this->getLayoutCurrentConfig());
    $form['layout_settings'] = $plugin->buildConfigurationForm($form['layout_settings'], $form_state);
    
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'save',
      '#button_type' => 'primary'
    ];
    return $form;
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // On applique la sousmission à partir de la function submit du layout.
    $subform_state = SubformState::createForSubform($form['layout_settings'], $form, $form_state);
    $this->getPluginForm($this->layout)->submitConfigurationForm($form['layout_settings'], $subform_state);
    //
    $plugin_id = $this->layout->getPluginId();
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