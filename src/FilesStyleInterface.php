<?php

namespace Drupal\generate_style_theme;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a files style entity type.
 */
interface FilesStyleInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
