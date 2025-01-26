<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\generate_style_theme\Services\ManageFileCustomStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;

/**
 * Formulaire pour la configuration de mon module
 */
class GenerateStyleThemeStyles extends ConfigFormBase {
  private static $key = 'generate_style_theme.styles';
  /**
   *
   * @var string
   */
  protected $path;
  
  /**
   *
   * @var ManageFileCustomStyle
   */
  protected $ManageFileCustomStyle;
  
  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;
  
  /**
   *
   * @var string|int
   */
  protected string|int $files_style_id;
  
  function __construct(ConfigFactoryInterface $config_factory, ManageFileCustomStyle $ManageFileCustomStyle, RouteMatchInterface $route_match) {
    parent::__construct($config_factory);
    $this->ManageFileCustomStyle = $ManageFileCustomStyle;
    $this->routeMatch = $route_match;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'), $container->get('generate_style_theme.manage_file_custom_style'), $container->get('current_route_match'));
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return self::$key;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::$key
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->files_style_id = $this->routeMatch->getParameter('files_style_id');
    // Champ de type 'details' (section repliable)
    $form['details_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Manage styles'),
      '#open' => TRUE,
      '#tree' => TRUE
    ];
    $default_css = '';
    $default_js = '';
    $style = null;
    if ($this->files_style_id)
      $style = \Drupal\generate_style_theme\Entity\FilesStyle::loadByName($this->files_style_id, 'generate_style_theme');
    if ($style) {
      $form['details_section']['#open'] = FALSE;
      $default_css = $style->getScss();
      $default_js = $style->getJs();
      // Ajouter un bouton à l'intérieur du champ 'details'
      $form['details_section']['custom_link'] = [
        '#type' => 'link',
        '#title' => $this->t('Ajouter les styles personnalisés'),
        '#url' => Url::fromRoute('generate_style_theme.managecustom.styles', [
          'files_style_id' => 'new'
        ]),
        '#attributes' => [
          'class' => [
            'button'
          ]
        ]
      ];
    }
    // le lable
    if (!$style)
      $form['details_section']['label_style'] = [
        '#type' => 'machine_name',
        '#title' => $this->t('Unique label for your style'),
        '#required' => TRUE,
        '#description' => $this->t('Enter a unique identifier in lowercase, without spaces, with dashes.'),
        '#machine_name' => [
          'exists' => [
            $this,
            'CheckIfLabelExist'
          ]
        ],
        '#disabled' => $style ? true : false
      ];
    else {
      $form['current_style'] = [
        '#type' => 'html_tag',
        '#tag' => 'h5',
        '#value' => $style->label()
      ];
      $form['details_section']['label_style'] = [
        '#type' => 'hidden',
        '#value' => $style->label()
      ];
    }
    // la liste des styles.
    // $links = [];
    // $styles =
    // \Drupal::entityTypeManager()->getStorage('files_style')->loadByProperties([
    // 'module' => 'generate_style_theme'
    // ]);
    /**
     *
     * @var \Drupal\generate_style_theme\FilesStyleListBuilder $ListBuilder
     */
    $ListBuilder = \Drupal::entityTypeManager()->getListBuilder('files_style');
    /**
     *
     * @var \Drupal\Core\Entity\Query\QueryInterface $query
     */
    $query = $ListBuilder->getQuery();
    $query->condition('module', 'generate_style_theme');
    $ListBuilder->setQuery($query);
    // foreach ($styles as $value) {
    // $links[] = [
    // '#type' => 'link',
    // '#title' => $value->label(),
    // '#url' => Url::fromRoute('generate_style_theme.managecustom.styles', [
    // 'files_style_id' => $value->label()
    // ])
    // ];
    // }
    $form['details_section']['lists'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      $ListBuilder->render()
    ];
    
    //
    $form['file_scss'] = [
      '#type' => 'textarea',
      '#title' => $this->t(' Custom scss '),
      '#default_value' => $default_css,
      '#rows' => '30',
      '#attributes' => [
        'class' => [
          'codemirror',
          'lang_scss'
        ]
      ],
      "#description" => "Vous pouvez ajouter les mixins et les librairies inclut dans @stephane888/wbu-atomique"
    ];
    $form['file_js'] = [
      '#type' => 'textarea',
      '#title' => $this->t(' Custom js '),
      '#default_value' => $default_js,
      '#rows' => '30',
      '#attributes' => [
        'class' => [
          'codemirror',
          'lang_js'
        ]
      ],
      "#description" => "Vous pouvez ajouter les mixins et les librairies inclut dans @stephane888/wbu-atomique"
    ];
    $form['#attached']['library'][] = 'generate_style_theme/codemirror_admin';
    //
    return parent::buildForm($form, $form_state);
  }
  
  /**
   * --
   */
  public function CheckIfLabelExist($label) {
    return \Drupal\generate_style_theme\Entity\FilesStyle::loadByName($label, 'generate_style_theme') ? true : false;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_scss = $form_state->getValue('file_scss');
    $file_js = $form_state->getValue('file_js');
    $this->files_style_id = $this->routeMatch->getParameter('files_style_id');
    $key = null;
    if ($this->files_style_id) {
      if ($this->files_style_id == 'generate_style_theme.styles') {
        $key = self::$key;
      }
      else
        $key = $form_state->getValue([
          'details_section',
          'label_style'
        ]);
    }
    if ($key) {
      $entity = $this->ManageFileCustomStyle->saveStyle($key, 'generate_style_theme', $file_scss, $file_js);
      if ($this->files_style_id == 'new') {
        $this->messenger()->addStatus($this->t('New style added'));
        $url = Url::fromRoute('generate_style_theme.managecustom.styles', [
          'files_style_id' => $entity->label()
        ]);
        $form_state->setRedirectUrl($url);
      }
      else
        $this->messenger()->addStatus($this->t('Style update'));
    }
    else {
      $this->messenger()->addError("Les styles n'ont pas pu etre sauvegarder, voir les logs");
      $this->getLogger('generate_style_theme')->info("Error de sauvegarde de style SCSS : " . $file_scss);
      $this->getLogger('generate_style_theme')->info("Error de sauvegarde de style JS : " . $file_js);
    }
  }
}