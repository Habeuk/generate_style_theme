<?php

namespace Drupal\generate_style_theme;

use Drupal\domain\Entity\Domain;

class GenerateStyleThemeDomain {
  
  /**
   * - Recupere la liste des domaines non utilisé.
   */
  public static function getUnUseDomain($value = null, $entityTypeId = null) {
    $domaines = self::getAlldomaines();
    $UseDomain = self::getEntity($entityTypeId);
    // dump($UseDomain);
    // dump($domaines);
    $UnUseDomaines = [];
    foreach ($domaines as $k => $domaine) {
      if ($value == $k || !isset($UseDomain[$k])) {
        $UnUseDomaines[$k] = $domaine;
      }
    }
    return $UnUseDomaines;
  }
  
  /**
   * --
   */
  public static function getAlldomaines() {
    // $StorageDomain = \Drupal::entityTypeManager()->getStorage("domain");
    $query = \Drupal::entityQuery('domain');
    $domainIds = $query->execute();
    $domains = Domain::loadMultiple($domainIds);
    $hostnames = [];
    foreach ($domains as $domain) {
      $hostnames[$domain->id()] = $domain->get('name');
    }
    return $hostnames;
  }
  
  /**
   * Charge les entites deja creer.
   */
  public static function getEntity($entity_type_id = null) {
    $hostnames = [];
    // On recupere les domaines(entites) deja creer.
    if ($entity_type_id == 'config_theme_entity') {
      $entities = \Drupal::entityTypeManager()->getStorage($entity_type_id)->loadMultiple();
      
      foreach ($entities as $entity) {
        /**
         *
         * @var \Drupal\generate_style_theme\Entity\ConfigThemeEntity $entity
         */
        $hostname = $entity->getHostname();
        $hostnames[$hostname] = $hostname;
      }
    }
    else {
      // $query = \Drupal::entityQuery($entity_type_id);
      // $domainIds = $query->execute();
      // $entityType =
      // \Drupal::entityTypeManager()->getStorage($entity_type_id);
      // if ($entityType) {
      // $wbumenudomains = $entityType->loadMultiple($domainIds);
      // foreach ($wbumenudomains as $wbumenudomain) {
      // $hostnames[$wbumenudomain->getHostname()] =
      // $wbumenudomain->getHostname();
      // }
      // }
      \Drupal::messenger()->addWarning("On n'a pas encore developper cette logique : GenerateStyleThemeDomain.php");
    }
    return $hostnames;
  }
  
}