<?php

namespace Drupal\generate_style_theme\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\generate_style_theme\FilesStyleInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the files style entity class.
 *
 * @ContentEntityType(
 *   id = "files_style",
 *   label = @Translation("Files style"),
 *   label_collection = @Translation("Files styles"),
 *   label_singular = @Translation("files style"),
 *   label_plural = @Translation("files styles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count files styles",
 *     plural = "@count files styles",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\generate_style_theme\FilesStyleListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\generate_style_theme\Form\FilesStyleForm",
 *       "edit" = "Drupal\generate_style_theme\Form\FilesStyleForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "files_style",
 *   revision_table = "files_style_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer files style",
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
 *     "collection" = "/admin/content/files-style",
 *     "add-form" = "/files-style/add",
 *     "canonical" = "/files-style/{files_style}",
 *     "edit-form" = "/files-style/{files_style}/edit",
 *     "delete-form" = "/files-style/{files_style}/delete",
 *   },
 *   field_ui_base_route = "entity.files_style.settings",
 * )
 */
class FilesStyle extends RevisionableContentEntityBase implements FilesStyleInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   *
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
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

  public function setJs($value) {
    $this->set('js', $value);
    return $this;
  }

  public function getJs() {
    return $this->get('js')->value;
  }

  public function getModule() {
    return $this->get('module')->value;
  }

  public function getRouteName() {
    return $this->get('route_name')->value;
  }

  public function setRouteName($value) {
    $this->set('route_name', $value);
    return $this;
  }

  /**
   * Permet de determiner si le styles doit etre charger de maniere globale.
   *
   * @return boolean
   */
  public function IsGlobalAccess() {
    return $this->getRouteName() ? false : true;
  }

  /**
   *
   * @param string $key
   * @return self
   */
  public static function loadByName($key, $module) {
    $entities = \Drupal::entityTypeManager()->getStorage('files_style')->loadByProperties([
      'label' => $key,
      'module' => $module
    ]);
    if (!empty($entities)) {
      return reset($entities);
    }
  }

  /**
   * Load files style by route name pattern
   *
   * @param string $route_pattern
   * @return array
   */
  public static function loadByRoutePattern($route_pattern) {
    $entities = \Drupal::entityTypeManager()->getStorage('files_style')->loadByProperties([]);

    $matching_entities = [];
    /**
     * @var FilesStyle $entity
     */
    foreach ($entities as $entity) {
      $entity_route_name = $entity->getRouteName();
      if ($entity_route_name && str_contains($entity_route_name, $route_pattern)) {
        $matching_entities[] = $entity;
      }
    }
    return $matching_entities;
  }

  /**
   * Load files style by route name
   *
   * @param string $route_name
   * @return array
   */
  public static function loadByRouteName($route_name) {
    $entities = \Drupal::entityTypeManager()->getStorage('files_style')->loadByProperties([
      'module' => 'generate_style_theme',
    ]);
    $entities = array_filter($entities, function ($entity) use ($route_name) {
      /**
       *
       * @var \Drupal\generate_style_theme\Entity\FilesStyle $entity
       */
      return $entity->evaluate($route_name);
    });
    
    return $entities;
  }

  protected function evaluate($path) {
    // Convert path to lowercase. This allows comparison of the same path
    // with different case. Ex: /Page, /page, /PAGE.
    $pages = $this->getRouteName();
    if (!$pages) {
      return TRUE;
    }
    // Do not trim a trailing slash if that is the complete path.
    $path = $path === '/' ? $path : rtrim($path, '/');
    $path_alias = mb_strtolower(\Drupal::service('path_alias.manager')->getAliasByPath($path));
    $pathMatcher = \Drupal::service('path.matcher');
    return $pathMatcher->matchPath($path_alias, $pages) || (($path != $path_alias) && $pathMatcher->matchPath($path, $pages));
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
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

    $fields['scss'] = BaseFieldDefinition::create('text_long')->setRevisionable(TRUE)->setLabel('Scss')->setDisplayOptions('form', [
      'type' => 'textarea',
      'weight' => 10
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('view', [
      'type' => 'text_default',
      'label' => 'above',
      'weight' => 10
    ])->setDisplayConfigurable('view', TRUE);

    $fields['js'] = BaseFieldDefinition::create('text_long')->setRevisionable(TRUE)->setLabel('JS')->setDisplayOptions('form', [
      'type' => 'textarea',
      'weight' => 10
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('view', [
      'type' => 'text_default',
      'label' => 'above',
      'weight' => 10
    ])->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')->setRevisionable(TRUE)->setLabel(t('Author'))->setSetting('target_type', 'user')->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')->setDisplayOptions('form', [
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

    $fields['route_name'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Route name'))
      ->setDescription(t('Optional: the full or partial name of a route this style applies to. Enter one route per line. You can use wildcards like "entity.node.*" to match multiple routes.'))
      ->setRevisionable(TRUE)
      ->setRequired(FALSE)
      ->addConstraint('RouteNames')
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 12,
        'settings' => [
          'rows' => 6,
        ]
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'basic_string',
        'weight' => 12
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Authored on'))->setDescription(t('The time that the files style was created.'))->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'timestamp',
      'weight' => 20
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('form', [
      'type' => 'datetime_timestamp',
      'weight' => 20
    ])->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the files style was last edited.'));

    return $fields;
  }
}
