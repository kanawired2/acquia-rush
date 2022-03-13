<?php

namespace Drupal\layoutcomponents;

use Drupal\Core\Config\CachedStorage;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Component\Uuid\Pecl;
use Drupal\Core\Config\FileStorage;

/**
 * LcUpdateManager to update the LC entities.
 */
class LcUpdateManager {

  /**
   * Section options.
   *
   * @var \Drupal\Core\Config\CachedStorage $configStorage
   */
  protected $configStorage;

  /**
   * Section options.
   *
   * @var \Drupal\Core\Config\ConfigFactory $configFactory
   */
  protected $configFactory;

  /**
   * Section options.
   *
   * @var \Drupal\Component\Uuid\Pecl $uuid
   */
  protected $uuid;

  /**
   * LcUpdateManager constructor.
   *
   * @param \Drupal\Core\Config\CachedStorage $config_storage
   *   The Cached Storage service.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The Config factory service.
   * @param \Drupal\Component\Uuid\Pecl $uuid
   *   The uuid service.
   */
  public function __construct(CachedStorage $config_storage, ConfigFactory $config_factory, Pecl $uuid) {
    $this->configStorage = $config_storage;
    $this->configFactory = $config_factory;
    $this->uuid = $uuid;
  }

  /**
   * LcUpdateManager constructor.
   *
   * @param array $config_names
   *   The array with config names.
   * @param string $module
   *   The name of module.
   */
  public function updateConfig(array $config_names, $module) {
    $config_path    = drupal_get_path('module', $module) . '/config/install';
    $source         = new FileStorage($config_path);

    foreach ($config_names as $name) {
      $this->configStorage->write($name, $source->read($name));
      $this->configFactory->getEditable($name)->set('uuid', $this->uuid->generate())->save();
    }
  }

}
