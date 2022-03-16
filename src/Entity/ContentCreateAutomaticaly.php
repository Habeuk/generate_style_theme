<?php

namespace Drupal\generate_style_theme\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\node\Entity\Node;
use Drupal\block_content\Entity\BlockContent;

/**
 * Defines the Contenu creer automatiquement entity.
 *
 * @ConfigEntityType(
 *   id = "content_create_automaticaly",
 *   label = @Translation("Contenu creer automatiquement"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\generate_style_theme\ContentCreateAutomaticalyListBuilder",
 *     "form" = {
 *       "add" = "Drupal\generate_style_theme\Form\ContentCreateAutomaticalyForm",
 *       "edit" = "Drupal\generate_style_theme\Form\ContentCreateAutomaticalyForm",
 *       "delete" = "Drupal\generate_style_theme\Form\ContentCreateAutomaticalyDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\generate_style_theme\ContentCreateAutomaticalyHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "content_create_automaticaly",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "home_page",
 *     "contents",
 *     "blocks"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/content_create_automaticaly/{content_create_automaticaly}",
 *     "add-form" = "/admin/structure/content_create_automaticaly/add",
 *     "edit-form" = "/admin/structure/content_create_automaticaly/{content_create_automaticaly}/edit",
 *     "delete-form" = "/admin/structure/content_create_automaticaly/{content_create_automaticaly}/delete",
 *     "collection" = "/admin/structure/content_create_automaticaly"
 *   }
 * )
 */
class ContentCreateAutomaticaly extends ConfigEntityBase implements ContentCreateAutomaticalyInterface {
  
  /**
   * The Contenu creer automatiquement ID.
   *
   * @var string
   */
  protected $id;
  
  /**
   * The Contenu creer automatiquement label.
   *
   * @var string
   */
  protected $label;
  
  /**
   * Page d'accueil.
   */
  protected $home_page;
  
  /**
   * Contenus associer
   */
  protected $contents = [];
  
  /**
   * Contenus associer
   */
  protected $blocks;
  
  /**
   *
   * @var integer
   */
  protected $test_valeur;
  
  public function getHomePageId() {
    return $this->get('home_page');
  }
  
  public function setHomePageId(int $id) {
    $this->set('home_page', $id);
  }
  
  /**
   *
   * @return []
   */
  public function getContents() {
    return $this->get('contents');
  }
  
  public function addHomePageId($id) {
    $this->home_page['nid'] = $id;
    $this->set('home_page', $this->home_page);
  }
  
  public function addSubcontentHomePage($id) {
    $this->home_page['contents'][] = $id;
    $this->set('home_page', $this->home_page);
  }
  
  public function addSubBlocksHomePage($id) {
    $this->home_page['blocks'][] = $id;
    $this->set('home_page', $this->home_page);
  }
  
  /**
   * Ajouter une page qui a été sauvegardé.
   *
   * @param string $content
   */
  public function addPageContent(string $contentType, $id) {
    $this->contents[$contentType]['nid'] = $id;
    $this->set('contents', $this->contents);
  }
  
  public function AddSubContentToPage($contentType, $id) {
    $this->contents[$contentType]['contents'][] = $id;
    $this->set('contents', $this->contents);
  }
  
  public function AddSubBlockToPage($contentType, $blockId) {
    $this->contents[$contentType]['blocks'][] = $blockId;
    $this->set('contents', $this->contents);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    // dump($storage);
    // dump($this->toArray());
    // $this->;
    // die();
  }
  
  public function delete() {
    $datas = $this->entityTypeManager()->getStorage($this->entityTypeId)->load($this->id)->toArray();
    // Delete home page.
    if (!empty($datas['home_page']['nid'])) {
      $nodeHome = Node::load($datas['home_page']['nid']);
      if ($nodeHome)
        $nodeHome->delete();
    }
    // Delete orthers pages and blocks.
    if (!empty($datas['contents'])) {
      foreach ($datas['contents'] as $data) {
        if (!empty($data['nid'])) {
          $NodePage = Node::load($data['nid']);
          if ($NodePage)
            $NodePage->delete();
        }
        if (!empty($data['contents'])) {
          foreach ($data['contents'] as $nid) {
            $Node = Node::load($nid);
            if ($Node)
              $Node->delete();
          }
        }
        if (!empty($data['blocks'])) {
          foreach ($data['blocks'] as $id) {
            $block = BlockContent::load($id);
            if ($block)
              $block->delete();
          }
        }
      }
    }
    parent::delete();
  }
  
}
