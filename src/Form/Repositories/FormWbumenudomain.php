<?php

namespace Drupal\generate_style_theme\Form\Repositories;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\wbumenudomain\Entity\Wbumenudomain;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\generate_style_theme\Entity\ContentCreateAutomaticaly;

/**
 * utiliser par CreatePagesSiteForm.
 * Contient les methodes liées à l'entité Wbumenudomain
 *
 * @author stephane
 *        
 */
class FormWbumenudomain {
  
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  protected $ConfigFactory;
  
  function __construct(EntityTypeManagerInterface $EntityTypeManagerInterface, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $EntityTypeManagerInterface;
    $this->ConfigFactory = $config_factory;
  }
  
  /**
   * Permet de construire le formulaire de l'entite Wbumenudomain.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function buildFormWbumenudomain(array &$form, FormStateInterface $form_state, $datas = []) {
    
    /**
     * Si l'utilisateur revient sur cette etape, on recupere son precedant choix.
     */
    if ($form_state->has('entity_wbumenudomain')) {
      $entityWbumenudomain = $form_state->get('entity_wbumenudomain');
    }
    else
      /**
       * On cre l'entite à partir des données ($datas).
       *
       *
       * @var \Drupal\Core\Entity\EntityInterface $entityWbumenudomain
       */
      $entityWbumenudomain = $this->entityTypeManager->getStorage('wbumenudomain')->create($datas);
    
    /**
     * L'entité ainsi créer est sauvegardé de maniere temporaire.
     */
    $form_state->set('entity_wbumenudomain', $entityWbumenudomain);
    // On recupere l'entity de rendu.
    $form_display = EntityFormDisplay::collectRenderDisplay($entityWbumenudomain, 'default');
    // On construit le formulaire de rendu à partir de l'entite et le resultat => $form.
    $form_display->buildForm($entityWbumenudomain, $form, $form_state);
    // On sauvegarde ce dernier.( principalement pour des raisons de performances, car tout au long du processus on en aurra besoin. )
    $form_state->set('form_display_wbumenudomain', $form_display);
  }
  
  /**
   * On effectue la sauvegarde ou MAJ temporaire des données (donnée provenant du remplissage de l'utilisateur) dans la variable temporaire.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function temporySaveWbumenudomain(array &$form, FormStateInterface $form_state) {
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
       * extractFormValues permet de recuperer les données dans $form pour mettre dans $entity.
       */
      $form_display->extractFormValues($entity, $form, $form_state);
      $form_state->set('entity_wbumenudomain', $entity);
      // Pour acceder facilement au nom de domaine.
      $form_state->set('hostname', $entity->getHostname());
      // Pour acceder facilement aux elements de menu selectionnes.
      $ar = $entity->get('field_element_de_menu_valides')->first()->getValue();
      if (!empty($ar))
        $form_state->set('field_element_de_menu_valides', $ar['value']);
      // On intialise le domaine.
      $this->sauvegardeStatus($entity);
    }
  }
  
  /**
   * Permet d'enregistrer cette etape du processus.
   * Elle cree l'instance de configuration ( pour effectuer le suivie, la maj et la suppression ) si elle n'existe pas.
   */
  protected function sauvegardeStatus(Wbumenudomain $entity_wbumenudomain) {
    $config = $this->ConfigFactory->get('generate_style_theme.settings');
    $domain_id = $entity_wbumenudomain->getHostname();
    /**
     *
     * @var \Drupal\domain\Entity\Domain $storageDomain
     */
    $storageDomain = $this->entityTypeManager->getStorage('domain');
    $Domain = $storageDomain->load($domain_id);
    
    /**
     *
     * @var ContentCreateAutomaticaly $storageCreateAutomaticaly
     */
    $storageCreateAutomaticaly = $this->entityTypeManager->getStorage($config->get('storage_auto'));
    $contentCreateAutomaticaly = $storageCreateAutomaticaly->load($domain_id);
    if (!$contentCreateAutomaticaly) {
      $contentCreateAutomaticaly = $storageCreateAutomaticaly->create([
        'id' => $domain_id,
        'label' => $domain_id
      ]);
      $contentCreateAutomaticaly->save();
      \Drupal::messenger()->addStatus(' Le domaine ' . $Domain->getHostname() . ' a été initialisé ');
    }
  }
  
  /**
   * Creation de l'entite wbumenudomain.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /**
     *
     * @var Wbumenudomain $entity_wbumenudomain
     */
    $entity_wbumenudomain = $form_state->get('entity_wbumenudomain');
    $entity_wbumenudomain->save();
  }
  
}