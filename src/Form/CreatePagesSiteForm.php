<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\wbumenudomain\Entity\Wbumenudomain;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Component\Utility\Random;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\generate_style_theme\Form\Repositories\FormWbumenudomain;
use Drupal\generate_style_theme\Form\Repositories\FormEntity;

/**
 * Class CreatePagesSiteForm.
 * tutos :
 * https://www.webomelette.com/how-render-entity-field-widgets-inside-custom-form-drupal-8
 */
class CreatePagesSiteForm extends FormBase {
  protected $NombrePageMax = 3;
  protected static $field_domain_access = 'field_domain_access';
  protected static $field_domain_admin = 'field_domain_admin';
  protected static $storage_auto = 'content_create_automaticaly';
  protected static $key = 'hp___---__#';
  
  /**
   * Drupal\domain_config_ui\Config\ConfigFactory definition.
   *
   * @var \Drupal\domain_config_ui\Config\ConfigFactory
   */
  protected $configFactory;
  
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  
  /**
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $Connection;
  
  /**
   *
   * @var FormWbumenudomain
   */
  protected $FormWbumenudomain;
  
  /**
   *
   * @var FormEntity
   */
  protected $FormEntity;
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
    $instance->FormWbumenudomain = $container->get('generate_style_theme.create_auto_contents.wbumenudomain');
    $instance->FormEntity = $container->get('generate_style_theme.create_auto_contents.entity');
    
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
        $form['email-user-d'] = [
          '#type' => 'email',
          '#title' => 'Email du proproitaire de domaine',
          '#require' => true
        ];
        return $this->CreatePagesNextPage($form, $form_state);
      }
    }
    else {
      $form_state->set('page_num', 1);
      $this->FormWbumenudomain->buildFormWbumenudomain($form, $form_state);
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
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @param array $datas
   */
  protected function buildFormPrestataire(array &$form, FormStateInterface $form_state, $datas = []) {
    /**
     * Si l'utilisateur revient sur cette etape, on recupere son precedant choix.
     */
    if ($form_state->has('new_contents') && $form_state->get([
      'new_contents',
      'prestataires'
    ])) {
      $entity = $form_state->get([
        'new_contents',
        'prestataires'
      ]);
    }
    else
      $entity = $this->entityTypeManager->getStorage('node')->create([
        'type' => 'prestataires'
      ]);
    /**
     * On cree l'entite à partir des données.
     *
     * @var \Drupal\Core\Entity\EntityInterface $entityWbumenudomain
     */
    $contents = $form_state->get('new_contents');
    if (empty($contents)) {
      $contents = [];
    }
    // $contents['prestataires'] = $entity;
    // $form_state->set('new_contents', $contents);
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
   * NB: cette fonction s'execute apres la validation.
   *
   * @param array $form
   *        An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *        The current state of the form.
   */
  public function CreatePageSubmitNext(array &$form, FormStateInterface $form_state) {
    $this->FormWbumenudomain->temporySaveWbumenudomain($form, $form_state);
    //
    $this->CreateInstanceHomePage($form_state);
    $this->FormEntity->createInstancePageItemsMenu($form_state);
    // On doit recuperer les données dans le formulaire utiliser et les mettres dans l'entité avant d'effectuer la validation.
    $this->temporySavePrestataireNode($form, $form_state);
    //
    $n = $form_state->get('page_num');
    // dump('last page : ', $n);
    $form_state->set('page_num', $n + 1)->setRebuild(TRUE);
  }
  
  /**
   * Creer l'intances de la page d'accueil.
   * On a opté de mettre tous les contenus dans 'new_contents' mais la page d'accueil est particuliere, car elle peut etre modifier par l'utilisateur.
   * Donc, on va fixe sont id avec une valeur non valide pour les champs machine_name(afin d'eviter les conflits avec les type de contenu).
   * on fixe: hp___---__#
   * On va egalement remettre 'new_contents' à [] si le model de page d'accueil est modifié.
   *
   * Apres l'exection de cette fonction, on a l'instance de la page d'accueil qui est crée et les instances de pages lies au menu.
   *
   * @param FormStateInterface $form_state
   */
  protected function CreateInstanceHomePage(FormStateInterface $form_state) {
    /**
     *
     * @var Wbumenudomain $entity_wbumenudomain
     */
    $entity_wbumenudomain = $form_state->get('entity_wbumenudomain');
    
    // Creation de l'instance de la page d'accueil.
    $homePageContentType = $entity_wbumenudomain->getContentTypeHomePage();
    if (!empty($homePageContentType)) {
      // si cest la meme page de home. on passe à la suite.
      if ($form_state->has([
        'new_contents',
        self::$key
      ])) {
        /**
         *
         * @var \Drupal\node\Entity\Node $homePage
         */
        $homePage = $form_state->get([
          'new_contents',
          self::$key
        ]);
        if ($homePage->bundle() == $homePageContentType) {
          return;
        }
      }
      
      // Une page doit etre unique, on se rassure qu'elle n'existe pas deja pour le domaine.
      $query = " SELECT nid from {node_field_data} as fd ";
      $query .= " inner join {node__field_domain_access} as fda ON fda.entity_id = fd.nid ";
      $query .= " where fd.type ='" . $homePageContentType . "' and fda.field_domain_access_target_id = '" . $entity_wbumenudomain->getHostname() . "' ";
      $Ids = $this->Connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
      if (!empty($Ids)) {
        \Drupal::messenger()->addWarning(" Un model existe deja pour le domaine %domaine, veillez la supprimer avant de creer une nouvelle ", [
          '%domaine' => $entity_wbumenudomain->getHostname()
        ]);
        return;
      }
      
      // Soit c'est la premiere fois que l'utilisateur execute next, soit ce dernier à modifier la page d'accueil.
      $contents = [];
      $contents[self::$key] = $this->createNode([
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
      // on ne peut pas dupliquer le contenu à ce niveau...
      // $this->createNewEntityReference($contents[$key]);
      $form_state->set('new_contents', $contents);
    }
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
    $form_state->set('page_num', $n)->setRebuild(TRUE);
  }
  
  /**
   * NB: elle s'execute à chaque submit.
   * (bouton trigger, #ajax, add_more).
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // On empeche la verification si c'est une action liée à un bouton add more.
    $button = $form_state->getTriggeringElement();
    if (in_array('add_more', $button['#parents']))
      return true;
    // Validation de l'email
    if ($form_state->has('email-user-d')) {
      if (\Drupal::entityQuery('user')->condition('mail', $form_state->get('email-user-d'))->execute()) {
        $form_state->setErrorByName('email-user-d', ' Ce email existe deja ');
      }
    }
    //
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
        $form_state->setErrorByName($field_name, ' Erreur sur le champs ' . $field_name . ' du type de contenu : ' . $content->bundle());
      }
    }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity_wbumenudomain = $form_state->get('entity_wbumenudomain');
    /**
     * On sauvgarde la page d'accueil.
     */
    if (self::$demo) {
      /**
       * Creation de l'entite wbumenudomain.
       *
       * @var Wbumenudomain $entity_wbumenudomain
       */
      dump($entity_wbumenudomain->toArray());
    }
    else
      $this->FormWbumenudomain->submitForm($form, $form_state);
    
    /**
     * Creation du node prestataures.
     *
     * @var \Drupal\node\Entity\Node $entity_node
     */
    // $entity_node = $form_state->get('entity_node');
    // if (self::$demo)
    // dump($entity_node->toArray());
    // else
    // $entity_node->save();
    
    // Enregistre en masse les nodes.
    $this->FormEntity->submitForm($form, $form_state);
    // /**
    // * Creation des pages (page d'accuiel et des pages selectionnées au niveau du menu.
    // * NB: cest
    // *
    // * @var Array $contents
    // */
    // $contents = $form_state->get('new_contents');
    // foreach ($contents as $k => $content) {
    // /**
    // *
    // * @var \Drupal\node\Entity\Node $content
    // */
    // if (self::$demo)
    // dump($content->toArray());
    // else {
    // $this->createNewEntityReference($contents[$k]);
    // $contents[$k]->save();
    // }
    // }
    // $message = 'Les differentes pages ont été crée';
    // \Drupal::messenger()->addMessage($message);
    if (self::$demo)
      die();
    // Creation de l'utilisateur.
    if (!self::$demo)
      $this->createUserByDomain($entity_wbumenudomain, $form_state);
    
    // Redirection vers la page de creation de theme.
    $contents = $form_state->get('new_contents');
    /**
     *
     * @var Node $node
     */
    $node = $contents[self::$key];
    // remove it
    \Drupal::messenger()->addStatus(" Identifiant du node : " . $node->isNew());
    if ($node) {
      $url = Url::fromRoute('entity.config_theme_entity.add_form', [], [
        'query' => [
          'content-type-home' => $node->bundle(),
          'content-type-home-id' => $node->id(),
          'lirairy' => $entity_wbumenudomain->getLirairy(),
          'domaine-id' => $entity_wbumenudomain->getHostname()
        ]
      ]);
      $form_state->setRedirectUrl($url);
    }
  }
  
  /**
   * Permet de creer un utilisateur avec les droits necessaire pour pouvoir manager le domaine encours.
   *
   * @param FormStateInterface $form_state
   */
  private function createUserByDomain(Wbumenudomain $entity_wbumenudomain, FormStateInterface $form_state) {
    $hostName = $entity_wbumenudomain->getHostname();
    $typeContenuHomePage = $entity_wbumenudomain->getContentTypeHomePage();
    $email = $form_state->getValue('email-user-d');
    $random = new Random();
    $password = $random->string(12, true);
    $user = User::create([
      'name' => $hostName,
      'pass' => $password,
      'mail' => empty($email) ? 'auto' . rand(1, 999) . $random->name() . '@example.com' : $email,
      'notify' => false
    ]);
    
    /**
     * On donne les access par rapport au domaine.
     */
    //
    if ($user->hasField(self::$field_domain_access)) {
      $user->get(self::$field_domain_access)->setValue([
        [
          'target_id' => $hostName
        ]
      ]);
    }
    //
    if ($user->hasField(self::$field_domain_admin)) {
      $user->get(self::$field_domain_admin)->setValue([
        [
          'target_id' => $hostName
        ]
      ]);
    }
    /**
     * On definie le role.
     */
    $rid = 'prestataires';
    if ($typeContenuHomePage == 'model_d_affichage_theme_commerce') {
      $rid = 'vendeurs';
    }
    elseif ($typeContenuHomePage == 'model_d_affichage_architecte_') {
      $rid = 'architecte';
    }
    $user->addRole($rid);
    $user->save();
    /**
     * \Drupal\Component\Render\MarkupInterface
     */
    $message = ' Nouveau compte créé, bien vouloir noter ces identifiants.';
    \Drupal::messenger()->addMessage($message);
    $message = ' Login : ' . $hostName;
    $message .= ' Password : ' . $password;
    \Drupal::messenger()->addMessage($message);
  }
  
  private function createNode(array $values) {
    return Node::create($values);
  }
  
  /**
   * On enregistre l'instance de node prestataire.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function temporySavePrestataireNode(array &$form, FormStateInterface $form_state) {
    // Il faut se rassurer que l'utilisateur passe à l'etape 2.
    if ($form_state->has('form_display_node') && $form_state->get('page_num') == 2) {
      // \Drupal::messenger()->addMessage(" temporySavePrestataireNode ");
      /**
       *
       * @var EntityFormDisplay $form_display_node
       */
      $form_display_node = $form_state->get('form_display_node');
      /**
       *
       * @var \Drupal\node\Entity\Node $entity_node
       */
      if ($form_state->has([
        'new_contents',
        'prestataires'
      ]))
        $entity_node = $form_state->get([
          'new_contents',
          'prestataires'
        ]);
      else
        $entity_node = $this->createNode([
          'type' => 'prestataires'
        ]);
      $form_display_node->extractFormValues($entity_node, $form, $form_state);
      $form_state->set([
        'new_contents',
        'prestataires'
      ], $entity_node);
    }
  }
  
}
