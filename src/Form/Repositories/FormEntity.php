<?php

namespace Drupal\generate_style_theme\Form\Repositories;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\wbumenudomain\Entity\Wbumenudomain;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\generate_style_theme\Entity\ContentCreateAutomaticaly;
use Drupal\Component\Serialization\Json;
use Drupal\generate_style_theme\Services\MenusToPageCreate;
use Drupal\Core\Database\Connection;
use Drupal\node\Entity\Node;
use Drupal\generate_style_theme\Services\CreateEntityFromWidget;

class FormEntity {
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  protected $ConfigFactory;
  protected static $field_domain_access = 'field_domain_access';
  protected static $field_domain_admin = 'field_domain_admin';
  private $contentCreateAutomaticaly = null;
  protected static $key = 'hp___---__#';
  
  /**
   *
   * @var Connection
   */
  protected $Connection;
  
  /**
   *
   * @var MenusToPageCreate
   */
  protected $MenusToPageCreate;
  
  /**
   *
   * @var CreateEntityFromWidget
   */
  protected $CreateEntityFromWidget;
  
  function __construct(EntityTypeManagerInterface $EntityTypeManagerInterface, ConfigFactoryInterface $config_factory, MenusToPageCreate $MenusToPageCreate, Connection $Connection, CreateEntityFromWidget $CreateEntityFromWidget) {
    $this->entityTypeManager = $EntityTypeManagerInterface;
    $this->ConfigFactory = $config_factory;
    $this->MenusToPageCreate = $MenusToPageCreate;
    $this->Connection = $Connection;
    $this->CreateEntityFromWidget = $CreateEntityFromWidget;
  }
  
  /**
   * Creation des pages (page d'accuiel, page prestataire et des pages selectionnées au niveau du menu.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function submitForm($form, FormStateInterface $form_state) {
    /**
     *
     * @var Wbumenudomain $entity
     */
    $entity = $form_state->get('entity_wbumenudomain');
    /**
     *
     * @var Wbumenudomain $entity
     */
    $entity = $form_state->get('entity_wbumenudomain');
    $this->createContentCreateAutomatically($entity);
    //
    // Enregistre en masse les nodes.
    $contents = $form_state->get('new_contents');
    foreach ($contents as $k => $content) {
      /**
       *
       * @var \Drupal\node\Entity\Node $content
       */
      $this->createNewEntityReference($content);
      $content->save();
      $contents[$k] = $content;
      if ($k == self::$key) {
        $this->contentCreateAutomaticaly->addHomePageId($content->id());
      }
      else
        $this->contentCreateAutomaticaly->addPageContent($content->bundle(), $content->id());
      // on sauvegarde les pages au fur et à mesure.
      $this->contentCreateAutomaticaly->save();
    }
    //
    $form_state->set('new_contents', $contents);
    
    $message = ' Les differentes pages ont été crée ';
    \Drupal::messenger()->addMessage($message);
  }
  
  /**
   */
  private function sauvegardeStatus(Wbumenudomain $entity_wbumenudomain, Node $content, $isHomePage = false) {
    //
  }
  
  function createContentCreateAutomatically(Wbumenudomain $entity_wbumenudomain) {
    $config = $this->ConfigFactory->get('generate_style_theme.settings');
    $domain_id = $entity_wbumenudomain->getHostname();
    
    /**
     *
     * @var ContentCreateAutomaticaly $storageCreateAutomaticaly
     */
    $storageCreateAutomaticaly = $this->entityTypeManager->getStorage($config->get('storage_auto'));
    $contentCreateAutomaticaly = $storageCreateAutomaticaly->load($domain_id);
    if ($contentCreateAutomaticaly)
      $this->contentCreateAutomaticaly = $contentCreateAutomaticaly;
  }
  
  /**
   * Lors de la creation d'un nouveau entity ses entitées reference doivent etre dupliquer.
   */
  private function createNewEntityReference(&$entity) {
    $fields = $entity->getFields();
    if ($this->contentCreateAutomaticaly)
      foreach ($fields as $field) {
        /**
         *
         * @var \Drupal\Core\Field\FieldItemList $field
         */
        /**
         *
         * @var \Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget $widget
         */
        $widget = $this->getWidget($entity, $field);
        if ($widget && $widget->getPluginId() == 'wbumenudomainhost_complex_inline') {
          $this->CreateEntityFromWidget->createEntity($widget, $entity, $field, $this->contentCreateAutomaticaly);
        }
      }
  }
  
  /**
   *
   * @param object $entity
   * @param object $field
   * @return \Drupal\Core\Field\PluginSettingsInterface|NULL
   */
  public function getWidget($entity, $field) {
    /**
     *
     * @var \Drupal\Core\Entity\Entity\EntityFormDisplay $entity_form_display
     */
    $entity_form_display = \Drupal::service('entity_display.repository')->getFormDisplay($entity->getEntityTypeId(), $entity->bundle());
    $widget = $entity_form_display->getRenderer($field->getFieldDefinition()->getName());
    return $widget;
  }
  
  /**
   * Cree les instances de pages qui ont été selectionné sur le menu.
   * Cela permet d'initialiser les pages.
   * un tableau de données se cree lors du passage de l'etape 1 => 2.
   * La validation de ces données se fait de 2 => 3.
   */
  public function createInstancePageItemsMenu(FormStateInterface $form_state) {
    $hostname = $form_state->get('hostname');
    $field_element_de_menu_valides = $form_state->get('field_element_de_menu_valides');
    if ($hostname && $field_element_de_menu_valides) {
      $Contents = $form_state->get('new_contents');
      if ($Contents) {
        foreach ($this->MenusToPageCreate->getListPageToCreate(json::decode($field_element_de_menu_valides)) as $content) {
          if (empty($Contents[$content['type']])) {
            // Une page doit etre unique, on se rassure qu'elle n'existe pas deja pour le domaine.
            $query = " SELECT nid from {node_field_data} as fd ";
            $query .= " inner join {node__field_domain_access} as fda ON fda.entity_id = fd.nid ";
            $query .= " where fd.type ='" . $content['type'] . "' and fda.field_domain_access_target_id = '" . $hostname . "' ";
            $Ids = $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($Ids)) {
              $ar = $content;
              $ar[self::$field_domain_access] = [
                [
                  'target_id' => $hostname
                ]
              ];
              $Contents[$content['type']] = $this->createNode($ar);
            }
            else {
              \Drupal::messenger()->addWarning(" La page existe deja:  " . $content['type'] . ' pour le domaine ' . $hostname);
            }
          }
        }
        $form_state->set('new_contents', $Contents);
      }
      else
        \Drupal::messenger()->addWarning(" Aucune page supplementaire ne peut etre creer si la page d'accueil n'est pas definit ");
    }
    else
      \Drupal::messenger()->addWarning(" Le nom d'hote n'est pas definit ");
  }
  
  private function createNode(array $values) {
    return Node::create($values);
  }
  
}