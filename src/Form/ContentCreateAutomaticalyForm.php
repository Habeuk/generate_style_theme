<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wbumenudomain\Wbumenudomain;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Stephane888\HtmlBootstrap\ThemeUtility;
use Drupal\node\Entity\Node;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class ContentCreateAutomaticalyForm.
 */
class ContentCreateAutomaticalyForm extends EntityForm {
  /**
   *
   * @var ThemeUtility
   */
  protected $ThemeUtility;
  
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->ThemeUtility = $container->get('generate_style_theme.theme-utility');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    
    /**
     *
     * @var \Drupal\generate_style_theme\Entity\ContentCreateAutomaticaly $content_create_automaticaly
     */
    $content_create_automaticaly = $this->entity;
    // dump($content_create_automaticaly->toArray());
    
    //
    $form['label'] = [
      '#type' => 'select',
      '#title' => $this->t(' Domaine '),
      '#maxlength' => 255,
      '#default_value' => $content_create_automaticaly->label(),
      '#description' => $this->t(" Selectionne le domaine "),
      '#required' => TRUE,
      '#options' => $this->getAllDomains()
    ];
    
    //
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $content_create_automaticaly->id(),
      '#machine_name' => [
        'exists' => '\Drupal\generate_style_theme\Entity\ContentCreateAutomaticaly::load'
      ],
      '#disabled' => !$content_create_automaticaly->isNew()
    ];
    
    //
    $this->ThemeUtility->addContainerTree('home_page', $form, 'Home page', true);
    $homeId = $content_create_automaticaly->get('home_page') !== null ? $content_create_automaticaly->get('home_page')['nid'] : null;
    if ($homeId) {
      $node = Node::load($homeId);
      if ($node)
        $form['home_page']['nid'] = [
          '#title' => $this->getNodeTypeLabel($node->bundle()) . ' : ' . $node->getTitle(),
          '#type' => 'textfield',
          '#attributes' => [
            "readonly" => 'readonly'
          ],
          '#default_value' => $homeId,
          '#description' => [
            [
              '#type' => 'link',
              '#title' => ' Voir ',
              '#url' => $node->toUrl()
            ],
            [
              '#type' => 'link',
              '#title' => ' Editer ',
              '#url' => $node->toUrl('edit-form')
            ],
            [
              '#type' => 'link',
              '#title' => ' Supprimer ',
              '#url' => $node->toUrl('delete-form')
            ]
          ]
        ];
    }
    
    //
    $this->ThemeUtility->addContainerTree('contents', $form, 'Pages associées', true);
    foreach ($content_create_automaticaly->getContents() as $bundle => $datas) {
      $this->ThemeUtility->addContainerTree($bundle, $form['contents'], $this->getNodeTypeLabel($bundle));
      if (!empty($datas['nid'])) {
        $node = Node::load($datas['nid']);
        if ($node)
          $form['contents'][$bundle]['nid'] = [
            '#title' => $this->getNodeTypeLabel($node->bundle()) . ' : ' . $node->getTitle(),
            '#type' => 'textfield',
            '#attributes' => [
              "readonly" => 'readonly'
            ],
            '#default_value' => $datas['nid'],
            '#description' => [
              [
                '#type' => 'link',
                '#title' => ' Voir ',
                '#url' => $node->toUrl()
              ],
              [
                '#type' => 'link',
                '#title' => ' Editer ',
                '#url' => $node->toUrl('edit-form')
              ],
              [
                '#type' => 'link',
                '#title' => ' Supprimer ',
                '#url' => $node->toUrl('delete-form')
              ]
            ]
          ];
      }
      
      if (!empty($datas['contents'])) {
        $this->ThemeUtility->addContainerTree('contents', $form['contents'][$bundle], 'Contenus');
        $this->renderNodeAssocier($datas['contents'], $form['contents'][$bundle]['contents']);
      }
      if (!empty($datas['blocks'])) {
        $this->ThemeUtility->addContainerTree('blocks', $form['contents'][$bundle], 'Blocks');
        $this->renderBlockAssocier($datas['blocks'], $form['contents'][$bundle]['blocks']);
      }
      //
    }
    
    return $form;
  }
  
  private function renderNodeAssocier(array $ids, &$form) {
    foreach ($ids as $id) {
      $node = Node::load($id);
      if ($node)
        $form[] = [
          '#title' => $this->getNodeTypeLabel($node->bundle()) . ' : ' . $node->getTitle(),
          '#type' => 'textfield',
          '#attributes' => [
            "readonly" => 'readonly'
          ],
          '#default_value' => $id,
          '#description' => [
            [
              '#type' => 'link',
              '#title' => ' Voir ',
              '#url' => $node->toUrl()
            ],
            [
              '#type' => 'link',
              '#title' => ' Editer ',
              '#url' => $node->toUrl('edit-form')
            ],
            [
              '#type' => 'link',
              '#title' => ' Supprimer ',
              '#url' => $node->toUrl('delete-form')
            ]
          ]
        ];
    }
  }
  
  private function renderBlockAssocier(array $ids, &$form) {
    foreach ($ids as $id) {
      $block = BlockContent::load($id);
      if ($block)
        $form[] = [
          '#title' => $this->getNodeTypeLabel($block->bundle()) . ' : ' . $block->label(),
          '#type' => 'textfield',
          '#attributes' => [
            "readonly" => 'readonly'
          ],
          '#default_value' => $id,
          '#description' => [
            [
              '#type' => 'link',
              '#title' => ' Editer ',
              '#url' => $block->toUrl('edit-form')
            ],
            [
              '#type' => 'link',
              '#title' => ' Supprimer ',
              '#url' => $block->toUrl('delete-form')
            ]
          ]
        ];
    }
  }
  
  private function getNodeTypeLabel($bundle) {
    $storageNodeType = $this->entityTypeManager->getStorage('node_type');
    $NodeType = $storageNodeType->load($bundle);
    if ($NodeType)
      return $NodeType->label();
    else
      return $bundle;
  }
  
  public function getAllDomains() {
    return Wbumenudomain::getAlldomaines();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $content_create_automaticaly = $this->entity;
    $status = $content_create_automaticaly->save();
    //
    switch ($status) {
      //
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t(' Created the %label Contenu creer automatiquement. ', [
          '%label' => $content_create_automaticaly->label()
        ]));
        break;
      
      default:
        $this->messenger()->addMessage($this->t(' Saved the %label Contenu creer automatiquement. ', [
          '%label' => $content_create_automaticaly->label()
        ]));
    }
    //
    $form_state->setRedirectUrl($content_create_automaticaly->toUrl('collection'));
  }
  
}
