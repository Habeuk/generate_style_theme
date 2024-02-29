<?php

namespace Drupal\generate_style_theme\Services;

/**
 *
 * @author stephane
 * @deprecated delete before 4x
 */
class MenusToPageCreate {
  protected $menuPage = [
    2 => null, // estimer mes travaux.
    3 => [
      'type' => 'cuisines_equipees',
      'title' => [
        [
          'value' => 'Cuisine haut de gamme'
        ]
      ]
    ],
    14 => [
      'type' => 'page_realisation',
      'title' => [
        [
          'value' => 'Nos plus belles réalisations'
        ]
      ]
    ],
    38 => [
      'type' => 'qui_sommes_nous',
      'title' => [
        [
          'value' => 'Qui sommes nous ?'
        ]
      ]
    ],
    56 => [
      'type' => 'page_tarif_rc_web_',
      'title' => [
        [
          'value' => 'Nos tarifs'
        ]
      ]
    ],
    1 => [
      'type' => 'comment_sa_marche',
      'title' => [
        [
          'value' => 'Comment ça marche'
        ]
      ]
    ],
    5 => null, // page parrainage
    6 => null, // blog
    7 => null, // form-vuejs
    8 => null, // form-vuejs
    9 => null, // form-vuejs
    10 => null, // form-vuejs
    11 => null, // form-vuejs
    12 => null,
    15 => null, // page guide nos travaux
    16 => [
      'type' => 'retructement',
      'title' => [
        [
          'value' => 'Retructement'
        ]
      ]
    ],
    22 => null,
    25 => 0,
    26 => null, // nos produits
    27 => null, // partenaires
    30 => null, // form-vuejs
    31 => null, // form-vuejs
    35 => null, // bonne affaire
    39 => 0,
    46 => null, // gerer les devis.
    57 => [
      'type' => 'page_d_equipe',
      'title' => [
        [
          'value' => 'Notre équipe'
        ]
      ]
    ],
    58 => [
      'type' => 'nos_services_rc_web_',
      'title' => [
        [
          'value' => 'Nos services'
        ]
      ]
    ]
  ];
  
  /**
   * Retourne, un array contenant les pages qui vont etre creer en function des
   * items du menu selectionnées.
   *
   * @param array $menuValue
   * @return NULL[]|number[]|string[][]|string[][][][]
   */
  function getListPageToCreate(array $menuValue) {
    $newContent = [];
    foreach ($menuValue as $value) {
      if ($value && !empty($this->menuPage[$value])) {
        $newContent[$value] = $this->menuPage[$value];
      }
    }
    return $newContent;
  }
  
}