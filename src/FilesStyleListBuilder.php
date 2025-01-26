<?php

namespace Drupal\generate_style_theme;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Url;

/**
 * Provides a list controller for the files style entity type.
 */
class FilesStyleListBuilder extends EntityListBuilder {
  
  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;
  /**
   *
   * @var QueryInterface
   */
  protected $customQuery;
  
  /**
   * Constructs a new FilesStyleListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *        The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *        The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *        The date formatter service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter) {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_formatter;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static($entity_type, $container->get('entity_type.manager')->getStorage($entity_type->id()), $container->get('date.formatter'));
  }
  
  /**
   *
   * @param QueryInterface $query
   * @return QueryInterface
   */
  public function getQuery(): QueryInterface {
    $this->customQuery = parent::getEntityListQuery();
    return $this->customQuery;
  }
  
  /**
   *
   * @param QueryInterface $query
   */
  public function setQuery(QueryInterface $query) {
    $this->customQuery = $query;
  }
  
  /**
   * Returns a query object for loading entity IDs from the storage.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface A query object used to
   *         load entity IDs.
   */
  protected function getEntityListQuery(): QueryInterface {
    if (!$this->customQuery)
      $this->customQuery = parent::getEntityListQuery();
    return $this->customQuery;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();
    
    $query = $this->getEntityListQuery();
    $total = $query->count()->execute();
    $build['summary']['#markup'] = $this->t('Total files styles: @total', [
      '@total' => $total
    ]);
    return $build;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['label'] = $this->t('Label');
    $header['status'] = $this->t('Status');
    $header['uid'] = $this->t('Author');
    $header['created'] = $this->t('Created');
    $header['changed'] = $this->t('Updated');
    return $header + parent::buildHeader();
  }
  
  /**
   * Gets this list's default operations.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *        The entity the operations are for.
   *        
   * @return array The array structure is identical to the return value of
   *         self::getOperations().
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = [];
    if ($entity->get('module')->value == 'generate_style_theme') {
      $delete_url = Url::fromRoute('generate_style_theme.managecustom.styles', [
        'files_style_id' => $entity->label()
      ]);
      $operations['edit_custom_editor'] = [
        'title' => $this->t('Edit via editor'),
        'weight' => -10,
        'url' => $delete_url
      ];
    }
    return $operations + parent::getDefaultOperations($entity);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\generate_style_theme\FilesStyleInterface $entity */
    $row['id'] = $entity->id();
    $row['label'] = $entity->label();
    $row['status'] = $entity->get('status')->value ? $this->t('Enabled') : $this->t('Disabled');
    $row['uid']['data'] = [
      '#theme' => 'username',
      '#account' => $entity->getOwner()
    ];
    $row['created'] = $this->dateFormatter->format($entity->get('created')->value);
    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime());
    return $row + parent::buildRow($entity);
  }
}
