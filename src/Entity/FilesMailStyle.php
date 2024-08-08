<?php
declare(strict_types = 1);

namespace Drupal\generate_style_theme\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\generate_style_theme\FilesMailStyleInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the filesmailstyle entity class.
 *
 * @ContentEntityType(
 *   id = "filesmailstyle",
 *   label = @Translation("Files Mail Style"),
 *   label_collection = @Translation("Files Mail Styles"),
 *   label_singular = @Translation("files mail style"),
 *   label_plural = @Translation("files mail styles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count files mail styles",
 *     plural = "@count files mail styles",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\generate_style_theme\FilesMailStyleListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\generate_style_theme\Form\FilesMailStyleForm",
 *       "edit" = "Drupal\generate_style_theme\Form\FilesMailStyleForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *       "revision-delete" = \Drupal\Core\Entity\Form\RevisionDeleteForm::class,
 *       "revision-revert" = \Drupal\Core\Entity\Form\RevisionRevertForm::class,
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "revision" = \Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider::class,
 *     },
 *   },
 *   base_table = "filesmailstyle",
 *   revision_table = "filesmailstyle_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer filesmailstyle",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "collection" = "/admin/content/generatesthe-filesmailstyle",
 *     "add-form" = "/generatesthe-filesmailstyle/add",
 *     "canonical" = "/generatesthe-filesmailstyle/{filesmailstyle}",
 *     "edit-form" = "/generatesthe-filesmailstyle/{filesmailstyle}/edit",
 *     "delete-form" = "/generatesthe-filesmailstyle/{filesmailstyle}/delete",
 *     "delete-multiple-form" = "/admin/content/generatesthe-filesmailstyle/delete-multiple",
 *     "revision" = "/generatesthe-filesmailstyle/{filesmailstyle}/revision/{filesmailstyle_revision}/view",
 *     "revision-delete-form" = "/generatesthe-filesmailstyle/{filesmailstyle}/revision/{filesmailstyle_revision}/delete",
 *     "revision-revert-form" = "/generatesthe-filesmailstyle/{filesmailstyle}/revision/{filesmailstyle_revision}/revert",
 *     "version-history" = "/generatesthe-filesmailstyle/{filesmailstyle}/revisions",
 *   },
 *   field_ui_base_route = "entity.filesmailstyle.settings",
 * )
 */
final class FilesMailStyle extends RevisionableContentEntityBase implements FilesMailStyleInterface {
  
  use EntityChangedTrait;
  use EntityOwnerTrait;
  
  /**
   *
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }
  
  public function setScss($value) {
    $this->set('scss', $value);
    return $this;
  }
  
  public function getScss() {
    return $this->get('scss')->value;
  }
  
  public function getModule() {
    return $this->get('module')->value;
  }
  
  /**
   *
   * @param string $key
   * @return self
   */
  public static function loadByName($key, $module) {
    $entities = \Drupal::entityTypeManager()->getStorage('filesmailstyle')->loadByProperties([
      'label' => $key,
      'module' => $module
    ]);
    if (!empty($entities)) {
      return reset($entities);
    }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    
    $fields['label'] = BaseFieldDefinition::create('string')->setRevisionable(TRUE)->setLabel(t('Label'))->setRequired(TRUE)->setSetting('max_length', 255)->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -5
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'string',
      'weight' => -5
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['status'] = BaseFieldDefinition::create('boolean')->setRevisionable(TRUE)->setLabel(t('Status'))->setDefaultValue(TRUE)->setSetting('on_label', 'Enabled')->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'settings' => [
        'display_label' => FALSE
      ],
      'weight' => 0
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('view', [
      'type' => 'boolean',
      'label' => 'above',
      'weight' => 0,
      'settings' => [
        'format' => 'enabled-disabled'
      ]
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['module'] = BaseFieldDefinition::create('string')->setLabel(t('Label'))->setRequired(TRUE)->setSetting('max_length', 255)->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -5
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'string',
      'weight' => -5
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['scss'] = BaseFieldDefinition::create('text_long')->setRevisionable(TRUE)->setLabel(t('Description'))->setDisplayOptions('form', [
      'type' => 'text_textarea',
      'weight' => 10
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('view', [
      'type' => 'text_default',
      'label' => 'above',
      'weight' => 10
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')->setRevisionable(TRUE)->setLabel(t('Author'))->setSetting('target_type', 'user')->setDefaultValueCallback(self::class . '::getDefaultEntityOwner')->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => 60,
        'placeholder' => ''
      ],
      'weight' => 15
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'author',
      'weight' => 15
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Authored on'))->setDescription(t('The time that the filesmailstyle was created.'))->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'timestamp',
      'weight' => 20
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('form', [
      'type' => 'datetime_timestamp',
      'weight' => 20
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the filesmailstyle was last edited.'));
    
    return $fields;
  }
  
}
