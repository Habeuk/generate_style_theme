<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\wbumenudomain\Entity\Wbumenudomain;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Component\Serialization\Json;
use Drupal\node\Entity\node;
use Drupal\Core\Url;

/**
 * Class CreatePagesSiteForm.
 * tutos :
 * https://www.webomelette.com/how-render-entity-field-widgets-inside-custom-form-drupal-8
 */
class CreatePagesSiteForm extends FormBase {
  protected $NombrePageMax = 3;
  protected static $field_domain_access = 'field_domain_access';
  
  /**
   * Drupal\domain_config_ui\Config\ConfigFactory definition.
   *
   * @var \Drupal\domain_config_ui\Config\ConfigFactory
   */
  protected $configFactory;
  
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   *
   * @var \Drupal\generate_style_theme\Services\MenusToPageCreate
   */
  protected $MenusToPageCreate;
  
  /**
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $Connection;
  
  /**
   *
   * @var \Drupal\generate_style_theme\Services\CreateEntityFromWidget
   */
  protected $CreateEntityFromWidget;
  /**
   *
   * @var boolean
   */
  protected static $demo = false;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->configFactory = $container->get('config.factory');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->MenusToPageCreate = $container->get('generate_style_theme.menustopagecreate');
    $instance->Connection = $container->get('database');
    $instance->CreateEntityFromWidget = $container->get('generate_style_theme.createentiyfromwidget');
    return $instance;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_pages_site_form';
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'my-5';
    $form['#attributes']['class'][] = 'mx-md-5';
    $form['#attributes']['class'][] = 'px-md-5';
    $this->buildHeader($form, $form_state);
    if ($form_state->has('page_num') && $form_state->get('page_num') > 1) {
      if ($form_state->get('page_num') == 2) {
        $this->buildFormPrestataire($form, $form_state);
        return $this->CreatePagesNextPage($form, $form_state);
      }
      else {
        return $this->CreatePagesNextPage($form, $form_state);
      }
    }
    else {
      $form_state->set('page_num', 1);
      $this->buildFormWbumenudomain($form, $form_state);
    }
    
    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions'
    ];
    
    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      // Custom submission handler for page 1.
      '#submit' => [
        '::CreatePageSubmitNext'
      ]
    ];
    
    return $form;
  }
  
  /**
   * Permet de construire le formulaire de l'entite Wbumenudomain.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function buildFormWbumenudomain(array &$form, FormStateInterface $form_state, $datas = []) {
    
    /**
     * Si l'utilisateur revient sur cette etape, on recupere son precedant choix.
     */
    if ($form_state->has('entity_wbumenudomain')) {
      $entityWbumenudomain = $form_state->get('entity_wbumenudomain');
    }
    else
      /**
       * On cre l'entite à partir des données.
       *
       * @var \Drupal\Core\Entity\EntityInterface $entityWbumenudomain
       */
      $entityWbumenudomain = $this->entityTypeManager->getStorage('wbumenudomain')->create($datas);
    
    $form_state->set('entity_wbumenudomain', $entityWbumenudomain);
    
    $form_display = EntityFormDisplay::collectRenderDisplay($entityWbumenudomain, 'default');
    $form_display->buildForm($entityWbumenudomain, $form, $form_state);
    $form_state->set('form_display_wbumenudomain', $form_display);
  }
  
  protected function buildFormPrestataire(array &$form, FormStateInterface $form_state, $datas = []) {
    /**
     * Si l'utilisateur revient sur cette etape, on recupere son precedant choix.
     */
    if ($form_state->has('entity_node')) {
      $entity = $form_state->get('entity_node');
    }
    else
      $entity = $this->entityTypeManager->getStorage('node')->create([
        'type' => 'prestataires'
      ]);
    /**
     * On cre l'entite à partir des données.
     *
     * @var \Drupal\Core\Entity\EntityInterface $entityWbumenudomain
     */
    
    $form_state->set('entity_node', $entity);
    //
    $form_display = EntityFormDisplay::collectRenderDisplay($entity, 'default');
    $form_display->buildForm($entity, $form, $form_state);
    $form_state->set('form_display_node', $form_display);
  }
  
  /**
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function buildHeader(array &$form, FormStateInterface $form_state) {
    $step = ($form_state->has('page_num')) ? $form_state->get('page_num') : 1;
    $form['conatiner'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'd-flex',
          'justify-content-between'
        ]
      ],
      '#weight' => -20
    ];
    $form['conatiner']['titre1'] = [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#value' => "Etape 1/3",
      '#attributes' => [
        'class' => [
          $step == 1 ? 'text-info' : 'text-muted',
          'mb-3'
        ]
      ]
    ];
    $form['conatiner']['titre2'] = [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#value' => "Etape 2/3",
      '#attributes' => [
        'class' => [
          $step == 2 ? 'text-info' : 'text-muted',
          'mb-3'
        ]
      ]
    ];
    $form['conatiner']['titre3'] = [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#value' => "Etape 3/3",
      '#attributes' => [
        'class' => [
          $step == 3 ? 'text-info' : 'text-muted',
          'mb-3'
        ]
      ]
    ];
    
    $form['description1'] = [
      '#type' => 'html_tag',
      '#tag' => 'h6',
      '#value' => " Selectionner le style et les pages à creer ",
      '#weight' => -19,
      "#access" => $step == 1 ? true : false
    ];
    $form['description2'] = [
      '#type' => 'html_tag',
      '#tag' => 'h6',
      '#value' => " Creation de la page prestataire pour lesroisdelareno",
      '#weight' => -19,
      "#access" => $step == 2 ? true : false
    ];
    $form['description3'] = [
      '#type' => 'html_tag',
      '#tag' => 'h6',
      '#value' => " Formulaire valider, creer les differents contenus ",
      '#weight' => -19,
      "#access" => $step == 3 ? true : false
    ];
    $form['separente'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'py-4'
        ]
      ],
      '#weight' => -15
    ];
  }
  
  /**
   * Provides custom submission handler for page 1.
   *
   * @param array $form
   *        An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *        The current state of the form.
   */
  public function CreatePageSubmitNext(array &$form, FormStateInterface $form_state) {
    $this->temporySaveWbumenudomain($form, $form_state);
    $this->temporySaveNode($form, $form_state);
    //
    
    $n = $form_state->get('page_num');
    // dump('last page : ', $n);
    $form_state->set('page_num', $n + 1)->setRebuild(TRUE);
  }
  
  /**
   * Builds the second step form (next page).
   *
   * @param array $form
   *        An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *        The current state of the form.
   *        
   * @return array The render array defining the elements of the form.
   */
  public function CreatePagesNextPage(array &$form, FormStateInterface $form_state) {
    $form['actions'] = [
      '#type' => 'actions'
    ];
    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      // Custom submission handler for 'Back' button.
      '#submit' => [
        '::CreatePagesBackPage'
      ],
      // We won't bother validating the required 'color' field, since they
      // have to come back to this page to submit anyway.
      '#limit_validation_errors' => []
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Submit')
    ];
    // dump("page : ", $form_state->get('page_num'));
    if (($form_state->get('page_num')) < $this->NombrePageMax) {
      $form['actions']['submit']['#submit'] = [
        '::CreatePageSubmitNext'
      ];
      $form['actions']['submit']['#value'] = $this->t('Next');
    }
    return $form;
  }
  
  /**
   * Provides custom submission handler for 'Back' button (page 2).
   *
   * @param array $form
   *        An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *        The current state of the form.
   */
  public function CreatePagesBackPage(array &$form, FormStateInterface $form_state) {
    $n = $form_state->get('page_num');
    if ($n > 1)
      $n = $n - 1;
    $form_state->set('page_num', $n)->
    // Since we have logic in our buildForm() method, we have to tell the form
    // builder to rebuild the form. Otherwise, even though we set 'page_num'
    // to 1, the AJAX-rendered form will still show page 2.
    setRebuild(TRUE);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /**
     *
     * @var Wbumenudomain $entity_wbumenudomain
     */
    $entity_wbumenudomain = $form_state->get('entity_wbumenudomain');
    // Creation de la page d'accueil.
    $homePageContentType = $entity_wbumenudomain->getContentTypeHomePage();
    if (!empty($homePageContentType)) {
      $contents = $form_state->get('new_contents');
      if (!$contents)
        $contents = [];
      $contents[$homePageContentType] = $this->createNode([
        'type' => $homePageContentType,
        'title' => [
          [
            "value" => "page d'accueil"
          ]
        ],
        self::$field_domain_access => [
          [
            'target_id' => $form_state->get('hostname')
          ]
        ],
        'field_domain_source' => [
          [
            'target_id' => $form_state->get('hostname')
          ]
        ]
      ]);
      $this->createNewEntityReference($contents[$homePageContentType]);
      $form_state->set('new_contents', $contents);
      $this->createNodeIfNotOccurrence($form, $form_state);
    }
    $this->validation($form_state);
    parent::validateForm($form, $form_state);
  }
  
  private function validation(FormStateInterface $form_state) {
    $contents = $form_state->get('new_contents');
    if (!$contents)
      $contents = [];
    foreach ($contents as $content) {
      /**
       *
       * @var \Drupal\node\Entity\Node $content
       */
      $status = $content->validate();
      foreach ($status->getFieldNames() as $field_name) {
        $form_state->setErrorByName($field_name, 'erreur sur le champs ' . $field_name . ' du type de contenu : ' . $content->bundle());
      }
    }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $this->temporySaveWbumenudomain($form, $form_state);
    // $this->temporySaveNode($form, $form_state);
    
    //
    /**
     *
     * @var Wbumenudomain $entity_wbumenudomain
     */
    $entity_wbumenudomain = $form_state->get('entity_wbumenudomain');
    $entity_wbumenudomain->validate();
    if (self::$demo)
      dump($entity_wbumenudomain->toArray());
    else
      $entity_wbumenudomain->save();
    
    /**
     * Prestataires.
     *
     * @var \Drupal\node\Entity\Node $entity_node
     */
    $entity_node = $form_state->get('entity_node');
    if (self::$demo)
      dump($entity_node->toArray());
    else
      $entity_node->save();
    
    // Enregistre en masse les nodes.
    $contents = $form_state->get('new_contents');
    foreach ($contents as $k => $content) {
      /**
       *
       * @var \Drupal\node\Entity\Node $content
       */
      if (self::$demo)
        dump($content->toArray());
      else {
        $this->createNewEntityReference($contents[$k]);
        $contents[$k]->save();
      }
    }
    $message = 'Les differentes pages ont été crée';
    \Drupal::messenger()->addMessage($message);
    if (self::$demo)
      die();
    // Redirection vers la page de creation de theme.
    $homePageContentType = $entity_wbumenudomain->getContentTypeHomePage();
    if (isset($contents[$homePageContentType])) {
      $url = Url::fromRoute('entity.config_theme_entity.add_form', [], [
        'query' => [
          'content-type-home' => $homePageContentType,
          'content-type-home-id' => $contents[$homePageContentType]->id(),
          'lirairy' => $entity_wbumenudomain->getLirairy(),
          'domaine-id' => $entity_wbumenudomain->getHostname()
        ]
      ]);
      $form_state->setRedirectUrl($url);
    }
  }
  
  private function createNode(array $values) {
    return node::create($values);
  }
  
  /**
   * - Lors de la creation d'un nouveau entity ses entitées reference doivent etre dupliquer
   */
  private function createNewEntityReference(&$entity) {
    $fields = $entity->getFields();
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
        if (!self::$demo)
          $this->CreateEntityFromWidget->createEntity($widget, $entity, $field);
      }
    }
  }
  
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
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function temporySaveWbumenudomain(array &$form, FormStateInterface $form_state) {
    if ($form_state->has('form_display_wbumenudomain') && $form_state->has('entity_wbumenudomain')) {
      /**
       *
       * @var EntityFormDisplay $form_display
       */
      $form_display = $form_state->get('form_display_wbumenudomain');
      
      /**
       *
       * @var Wbumenudomain $entity
       */
      $entity = $form_state->get('entity_wbumenudomain');
      /**
       * Cette fonction permet de recuperer les données dans $form pour mettre dans $entity.
       */
      $form_display->extractFormValues($entity, $form, $form_state);
      $form_state->set('entity_wbumenudomain', $entity);
      $form_state->set('hostname', $entity->getHostname());
      $ar = $entity->get('field_element_de_menu_valides')->first()->getValue();
      if (!empty($ar))
        $form_state->set('field_element_de_menu_valides', $ar['value']);
    }
  }
  
  /**
   * Enregistre le node prestataires.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function temporySaveNode(array &$form, FormStateInterface $form_state) {
    if ($form_state->has('form_display_node') && $form_state->has('entity_node')) {
      /**
       *
       * @var EntityFormDisplay $form_display_node
       */
      $form_display_node = $form_state->get('form_display_node');
      /**
       *
       * @var \Drupal\node\Entity\Node $entity_node
       */
      $entity_node = $form_state->get('entity_node');
      $form_display_node->extractFormValues($entity_node, $form, $form_state);
      $form_state->set('entity_node', $entity_node);
    }
  }
  
  /**
   * Cree un node s'il nya aucune equivalance pour le domaine.
   * un tableau de données se cree lors du passage de l'etape 1 => 2.
   * L valisation de ces données se fait de 2 => 3.
   */
  protected function createNodeIfNotOccurrence(array &$form, FormStateInterface $form_state) {
    $hostname = $form_state->get('hostname');
    $field_element_de_menu_valides = $form_state->get('field_element_de_menu_valides');
    if ($hostname && $field_element_de_menu_valides) {
      $Contents = $form_state->get('new_contents');
      if (!$Contents)
        $Contents = [];
      foreach ($this->MenusToPageCreate->getListPageToCreate(json::decode($field_element_de_menu_valides)) as $content) {
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
      }
      $form_state->set('new_contents', $Contents);
    }
    else {
      \Drupal::messenger()->addWarning(" Le nom d'hote n'est pas definit ");
    }
  }
  
}
