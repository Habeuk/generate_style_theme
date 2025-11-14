<?php

namespace Drupal\generate_style_theme\Services;

use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Service to validate paths.
 */
class RouteNamesValidatorService {

  use StringTranslationTrait;

  public function validate($paths_text) {
    $errors = [];
    
    if (empty($paths_text)) {
      return $errors;
    }

    $paths = $this->parsePaths($paths_text);
    
    if (empty($paths)) {
      return $errors;
    }

    $validation_result = $this->validatePaths($paths);
    
    if (!empty($validation_result['duplicates'])) {
      $errors[] = $this->t('Duplicate paths found: %paths.', [
        '%paths' => implode(', ', $validation_result['duplicates'])
      ]);
    }

    if (!empty($validation_result['invalid'])) {
      $errors[] = $this->t('The following paths are invalid: %paths. Each line must contain a valid path.', [
        '%paths' => implode(', ', $validation_result['invalid'])
      ]);
    }

    return $errors;
  }

  public function validatePaths(array $paths) {
    $result = [
      'duplicates' => [],
      'invalid' => [],
    ];

    $path_counts = array_count_values($paths);
    foreach ($path_counts as $path => $count) {
      if ($count > 1) {
        $result['duplicates'][] = $path;
      }
    }

    foreach ($paths as $path) {
      if (!$this->isValidPath($path)) {
        $result['invalid'][] = $path;
      }
    }

    return $result;
  }

  public function parsePaths($value) {
    $lines = explode("\n", $value);
    $paths = [];

    foreach ($lines as $line) {
      $line = trim($line);
      if (empty($line) || strpos($line, '#') === 0) {
        continue;
      }
      $paths[] = $line;
    }

    return $paths;
  }

  public function isValidPath($path) {
    if (strpos($path, '*') !== false) {
      return $this->isValidPathPattern($path);
    }
    
    try {
      $url = Url::fromUserInput($path);
      return $url->isRouted();
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  public function isValidPathPattern($pattern) {
    $pattern = ltrim($pattern, '/');
    if (!preg_match('/^[a-zA-Z0-9_\-\*\/]+$/', $pattern)) {
      return FALSE;
    }
    
    $segments = explode('/', $pattern);
    
    foreach ($segments as $segment) {
      if ($segment !== '*' && !preg_match('/^[a-zA-Z0-9_-]+$/', $segment)) {
        return FALSE;
      }
    }
    
    return TRUE;
  }

  public function getPaths($paths_text) {
    return $this->parsePaths($paths_text);
  }

}