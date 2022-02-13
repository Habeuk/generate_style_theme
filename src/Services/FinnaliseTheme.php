<?php

namespace Drupal\generate_style_theme\Services;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Stephane888\Debug\debugLog;

class FinnaliseTheme {
  
  static public function AfterFileThemeGenerate($form, FormStateInterface $form_state) {
    if (!empty($form_state->getBuildInfo()['callback_object'])) {
      /**
       *
       * @var \Drupal\generate_style_theme\Form\ConfigThemeEntityForm $configForm
       */
      $configForm = $form_state->getBuildInfo()['callback_object'];
      /**
       *
       * @var \Drupal\generate_style_theme\Entity\ConfigThemeEntity $Entity
       */
      $Entity = $configForm->getEntity();
      
      $url = Url::fromRoute('generate_style_theme.installtheme', [
        'themename' => $Entity->getHostname(),
        'domaine_id' => $Entity->getHostname()
      ]);
      $form_state->setRedirectUrl($url);
    }
  }
  
}