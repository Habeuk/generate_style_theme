<?php

declare(strict_types=1);

namespace Drupal\generate_style_theme;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a filesmailstyle entity type.
 */
interface FilesMailStyleInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
