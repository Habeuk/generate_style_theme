<?php

namespace Drupal\generate_style_theme\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\file\Entity\File;
use CaseConverter\CaseString;

/**
 * Defines the Config theme entity entity.
 *
 * @ingroup generate_style_theme
 *
 * @ContentEntityType(
 *   id = "config_theme_entity",
 *   label = @Translation("Config theme entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\generate_style_theme\ConfigThemeEntityListBuilder",
 *     "views_data" = "Drupal\generate_style_theme\Entity\ConfigThemeEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\generate_style_theme\Form\ConfigThemeEntityForm",
 *       "add" = "Drupal\generate_style_theme\Form\ConfigThemeEntityForm",
 *       "edit" = "Drupal\generate_style_theme\Form\ConfigThemeEntityForm",
 *       "delete" = "Drupal\generate_style_theme\Form\ConfigThemeEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\generate_style_theme\ConfigThemeEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\generate_style_theme\ConfigThemeEntityAccessControlHandler",
 *   },
 *   base_table = "config_theme_entity",
 *   translatable = FALSE,
 *   admin_permission = "administer config theme entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "hostname",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/config_theme_entity/{config_theme_entity}",
 *     "add-form" = "/admin/structure/config_theme_entity/add",
 *     "edit-form" = "/admin/structure/config_theme_entity/{config_theme_entity}/edit",
 *     "delete-form" = "/admin/structure/config_theme_entity/{config_theme_entity}/delete",
 *     "collection" = "/admin/structure/config_theme_entity",
 *   },
 *   field_ui_base_route = "config_theme_entity.settings"
 * )
 */
class ConfigThemeEntity extends ContentEntityBase implements ConfigThemeEntityInterface {
  use EntityChangedTrait;
  use EntityPublishedTrait;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id()
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getHostname() {
    return $this->get('hostname')->value;
  }
  
  /**
   * -
   */
  public function getLogo() {
    $fid = $this->get('logo')->first()->getValue();
    if (!empty($fid)) {
      $file = File::load($fid["target_id"]);
      if ($file) {
        // $new_filename = CaseString::title($file->getFilename())->snake();
        // $stream_wrapper = \Drupal::service('stream_wrapper_manager')->getScheme($file->getFileUri());
        // $new_filename_uri = "{$stream_wrapper}://logos/{$new_filename}";
        // file_move($file, $new_filename_uri);
        return ImageStyle::load('medium')->buildUri($file->getFileUri());
      }
    }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setHostname($name) {
    $this->set('hostname', $name);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }
  
  /**
   * Retourne la premiere ocurence trouvé.
   *
   * @return array ["name" => "bleu"
   *         "color" => "#7AE864"]
   */
  public function getColorPrimary() {
    return $this->get('color_primary')->first()->getValue();
  }
  
  public function getColorSecondaire() {
    return $this->get('color_secondaire')->first()->getValue();
  }
  
  public function getColorLinkHover() {
    return $this->get('color_link_hover')->first()->getValue();
  }
  
  /**
   *
   * @return mixed
   */
  public function getColorBackground() {
    return $this->get('wbubackground')->first()->getValue();
  }
  
  /**
   *
   * @return mixed
   */
  public function getLirairy() {
    if ($this->get('lirairy')->first())
      return $this->get('lirairy')->first()->getValue();
  }
  
  /**
   * --
   */
  public function getH1FontSize() {
    if ($this->get('h1_font_size')->first())
      return $this->get('h1_font_size')->first()->getValue();
  }
  
  /**
   * --
   */
  public function getH2FontSize() {
    if ($this->get('h2_font_size')->first())
      return $this->get('h2_font_size')->first()->getValue();
  }
  
  /**
   *
   * @return mixed
   */
  public function gettext_font_size() {
    if ($this->get('text_font_size')->first())
      return $this->get('text_font_size')->first()->getValue();
  }
  
  /**
   *
   * @return mixed
   */
  public function getspace_bottom() {
    if ($this->get('space_bottom')->first())
      return $this->get('space_bottom')->first()->getValue();
  }
  
  /**
   *
   * @return mixed
   */
  public function getspace_top() {
    if ($this->get('space_top')->first())
      return $this->get('space_top')->first()->getValue();
  }
  
  /**
   *
   * @return mixed
   */
  public function getspace_inner_top() {
    if ($this->get('space_inner_top')->first())
      return $this->get('space_inner_top')->first()->getValue();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    
    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    //
    $fields['hostname'] = BaseFieldDefinition::create('wbumenudomaineditlink')->setLabel(t(' Hostname ou nom de domaine '))->setRequired(TRUE)->setDisplayOptions('form', [
      'type' => 'wbumenudomainhost',
      'settings' => [],
      'weight' => -3
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setConstraints([
      'UniqueField' => []
    ]);
    $fields['lirairy'] = BaseFieldDefinition::create('list_string')->setLabel(t(' Selectionné un style pour ce domaine '))->setRequired(TRUE)->setDescription(t('Selectionner le nom de domaine'))->setSetting('allowed_values_function', [
      '\Drupal\wbumenudomain\Wbumenudomain',
      'getLibrairiesCurrentTheme'
    ])->setDisplayOptions('view', [
      'label' => 'above'
    ])->setDisplayOptions('form', [
      'type' => 'options_select',
      'settings' => [],
      'weight' => -3
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['logo'] = BaseFieldDefinition::create('image')->setLabel(' Logo ')->setRequired(TRUE)->setDisplayConfigurable('form', [
      'type' => 'image'
    ])->setDisplayConfigurable('view', TRUE)->setSetting("min_resolution", "150x120");
    
    $fields['color_primary'] = BaseFieldDefinition::create('color_theme_field_type')->setLabel(' Couleur primaire ')->setRequired(TRUE)->setDisplayConfigurable('form', [
      'type' => 'colorapi_color_display'
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['color_secondaire'] = BaseFieldDefinition::create('color_theme_field_type')->setLabel(" Couleur secondaire  ")->setRequired(TRUE)->setDisplayConfigurable('form', [
      'type' => 'colorapi_color_display'
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['color_link_hover'] = BaseFieldDefinition::create('color_theme_field_type')->setLabel(" Couleur des liens ")->setRequired(TRUE)->setDisplayConfigurable('form', [
      'type' => 'colorapi_color_display'
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['wbubackground'] = BaseFieldDefinition::create('color_theme_field_type')->setLabel(" Couleur d'arrière plan ")->setRequired(TRUE)->setDisplayConfigurable('form', [
      'type' => 'colorapi_color_display'
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['h1_font_size'] = BaseFieldDefinition::create('string')->setLabel(" Taille de la police de titre ")->setRequired(TRUE)->setDisplayConfigurable('form', [
      'type' => 'string_textfield'
    ])->setDisplayConfigurable('view', TRUE)->setDefaultValue('3.4rem');
    
    $fields['h2_font_size'] = BaseFieldDefinition::create('string')->setLabel(" Taille de la police de sous titre ")->setRequired(TRUE)->setDisplayConfigurable('form', [
      'type' => 'string_textfield'
    ])->setDisplayConfigurable('view', TRUE)->setDefaultValue('2.4rem');
    
    $fields['text_font_size'] = BaseFieldDefinition::create('string')->setLabel(" Taille de la police par defaut ")->setRequired(TRUE)->setDisplayConfigurable('form', [
      'type' => 'string_textfield'
    ])->setDisplayConfigurable('view', TRUE)->setDefaultValue('1.4rem');
    
    $fields['space_bottom'] = BaseFieldDefinition::create('string')->setLabel(" Espace du bas entre les blocs ")->setRequired(TRUE)->setDisplayConfigurable('form', [
      'type' => 'number'
    ])->setDisplayConfigurable('view', TRUE)->setDefaultValue(5);
    
    $fields['space_top'] = BaseFieldDefinition::create('string')->setLabel(" Espace du haut entre les blocs ")->setRequired(TRUE)->setDisplayConfigurable('form', [
      'type' => 'number'
    ])->setDisplayConfigurable('view', TRUE)->setDefaultValue(4);
    
    $fields['space_inner_top'] = BaseFieldDefinition::create('string')->setLabel(" Espace interne ")->setRequired(TRUE)->setDisplayConfigurable('form', [
      'type' => 'number'
    ])->setDisplayConfigurable('view', TRUE)->setDefaultValue(0.5);
    
    $fields['status']->setDescription(t(' A boolean indicating whether the Config theme entity is published. '))->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => -3
    ]);
    
    $fields['site_config'] = BaseFieldDefinition::create('wbumenudomaineditlink')->setLabel(t(' Information de configuration du domaine '))->setRequired(false)->setDisplayOptions('form', [
      'type' => 'wbumenudomainsiteconfig',
      'settings' => [],
      'weight' => -3
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setConstraints([
      'UniqueField' => []
    ]);
    
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Created'))->setDescription(t('The time that the entity was created.'));
    
    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the entity was last edited.'));
    
    return $fields;
  }
  
}