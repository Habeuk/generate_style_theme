<?php
declare(strict_types = 1);

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Generate style theme settings for this site.
 */
final class ManageHeaders extends ConfigFormBase {
  private static string $key = 'generate_style_theme.settings';
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'generate_style_theme_manage_headers';
  }
  
  /**
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      self::$key
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::$key);
    $header_tags = $config->get('header_tags') ?: [];
    
    $form['header_tags'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Type'),
        $this->t('URL'),
        $this->t('Crossorigin'),
        $this->t('As (for preload)'),
        $this->t('Media'),
        $this->t('Actions')
      ],
      '#prefix' => '<div id="header-tags-wrapper">',
      '#suffix' => '</div>'
    ];
    
    // We'll support up to 20 tags; we can use Ajax to add more.
    $max = 3;
    for ($i = 0; $i < $max; $i++) {
      $tag = $header_tags[$i] ?? [];
      
      $form['header_tags'][$i]['type'] = [
        '#type' => 'select',
        '#options' => [
          'preconnect' => $this->t('Preconnect'),
          'stylesheet' => $this->t('Stylesheet'),
          'preload' => $this->t('Preload'),
          'dns-prefetch' => $this->t('DNS Prefetch')
        ],
        '#default_value' => $tag['type'] ?? '',
        '#empty_value' => ''
      ];
      
      $form['header_tags'][$i]['href'] = [
        '#type' => 'url',
        '#title' => $this->t('URL'),
        '#title_display' => 'invisible',
        '#default_value' => $tag['href'] ?? '',
        '#size' => 40
      ];
      
      $form['header_tags'][$i]['crossorigin'] = [
        '#type' => 'select',
        '#options' => [
          '' => $this->t('- None -'),
          'TRUE' => 'TRUE',
          'anonymous' => $this->t('Anonymous'),
          'use-credentials' => $this->t('Use credentials')
        ],
        '#default_value' => $tag['crossorigin'] ?? ''
      ];
      
      $form['header_tags'][$i]['as'] = [
        '#type' => 'select',
        '#options' => [
          '' => $this->t('- None -'),
          'image' => $this->t('Image'),
          'font' => $this->t('Font'),
          'script' => $this->t('Script'),
          'style' => $this->t('Style'),
          'fetch' => $this->t('Fetch'),
          'document' => $this->t('Document')
        ],
        '#default_value' => $tag['as'] ?? '',
        '#states' => [
          'visible' => [
            ':input[name="header_tags[' . $i . '][type]"]' => [
              'value' => 'preload'
            ]
          ]
        ]
      ];
      
      $form['header_tags'][$i]['media'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Media'),
        '#title_display' => 'invisible',
        '#default_value' => $tag['media'] ?? '',
        '#placeholder' => $this->t('e.g., (min-width: 768px)'),
        '#size' => 20
      ];
      
      $form['header_tags'][$i]['remove'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Remove'),
        '#title_display' => 'invisible',
        '#default_value' => 0
      ];
    }
    
    $form['add_more'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add more rows'),
      '#submit' => [
        '::addMoreSubmit'
      ],
      '#ajax' => [
        'callback' => '::addMoreAjax',
        'wrapper' => 'header-tags-wrapper'
      ]
    ];
    
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration')
    ];
    
    return parent::buildForm($form, $form_state);
  }
  
  /**
   * Ajax callback to add more rows.
   */
  public function addMoreAjax(array &$form, FormStateInterface $form_state) {
    return $form['header_tags'];
  }
  
  /**
   * Submit handler for "Add more rows".
   */
  public function addMoreSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    // if ($form_state->getValue('example') === 'wrong') {
    // $form_state->setErrorByName(
    // 'message',
    // $this->t('The value is not correct.'),
    // );
    // }
    // @endcode
    parent::validateForm($form, $form_state);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $header_tags = [];
    $values = $form_state->getValue('header_tags');
    foreach ($values as $tag) {
      // Skip empty or marked for removal.
      if (!empty($tag['remove'])) {
        continue;
      }
      if (empty($tag['type']) || empty($tag['href'])) {
        continue;
      }
      $header_tags[] = [
        'type' => $tag['type'],
        'href' => $tag['href'],
        'crossorigin' => $tag['crossorigin'],
        'as' => $tag['as'],
        'media' => $tag['media']
      ];
    }
    $config = $this->config(self::$key);
    $config->set('header_tags', $header_tags);
    $config->save();
    parent::submitForm($form, $form_state);
  }
  
}
