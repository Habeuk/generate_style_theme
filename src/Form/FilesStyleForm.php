<?php

namespace Drupal\generate_style_theme\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the files style entity edit forms.
 */
class FilesStyleForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New files style %label has been created.', $message_arguments));
        $this->logger('generate_style_theme')->notice('Created new files style %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The files style %label has been updated.', $message_arguments));
        $this->logger('generate_style_theme')->notice('Updated files style %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.files_style.canonical', ['files_style' => $entity->id()]);

    return $result;
  }

}
