<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LayoutEnteteForm.
 */
class LayoutEnteteForm extends FormBase {
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
    $instance->ConfigFactory = $container->get('config.factory');
    return $instance;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $LayoutConfig = \Drupal::config(self::$LayoutBaseKey);
    // dump($LayoutConfig->getRawData());
    /**
     *
     * @var \Drupal\formatage_models\Plugin\Layout\Sections\Headers\FormatageModelsheader1 $pluginHeader
     */
    $pluginHeader = $this->layoutManager->createInstance(self::$plugin_id);
    // $pluginHeader->setContext($name, $context);
    // v2lesroisdelareno_kksa
    $currentSetting = $this->getLayoutCurrentConfig();
    
    // dump($currentSetting);
    $pluginHeader->setConfiguration($currentSetting);
    // dump($currentSetting['v2lesroisdelareno_kksa']);
    $form += $pluginHeader->buildConfigurationForm($form, $form_state);
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
