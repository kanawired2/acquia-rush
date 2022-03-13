<?php

namespace Drupal\layoutcomponents;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\path_alias\AliasManager;
use Drupal\Core\Path\CurrentPathStack;

/**
 * General class for page hooks.
 */
class LcPage implements ContainerInjectionInterface {

  /**
   * The ModuleHandler object.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The Config Factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The Alias Manager object.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected $aliasManager;

  /**
   * The Alias Manager object.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * {@inheritdoc}
   */
  public function __construct(ModuleHandler $module_handler, ConfigFactoryInterface $config_factory, RequestStack $request, AliasManager $alias_manager, CurrentPathStack $current_path_stack) {
    $this->moduleHandler = $module_handler;
    $this->configFactory = $config_factory;
    $this->request = $request->getCurrentRequest();
    $this->aliasManager = $alias_manager;
    $this->currentPathStack = $current_path_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('config.factory'),
      $container->get('request_stack'),
      $container->get('path_alias.manager'),
      $container->get('path.current')
    );
  }

  /**
   * Implements hook_page_attachments() for LC pages.
   *
   * @see \hook_page_attachments()
   */
  public function pageAttachments(&$page) {
    $page['#attached']['library'][] = 'layoutcomponents/layoutcomponents';
    $page['#attached']['library'][] = 'layoutcomponents/layoutcomponents.lateral';
    $page['#attached']['library'][] = 'layoutcomponents/layoutcomponents.modal';
  }

  /**
   * Implements hook_library_info_alter() for LC pages.
   *
   * @see \hook_library_info_alter()
   */
  public function libraryInfoAlter(&$libraries, $extension) {
    $_entity_form = $this->request->attributes->get('_entity_form');
    if ($_entity_form == 'node.layout_builder') {
      if (array_key_exists('drupal.dialog.off_canvas', $libraries)) {
        unset($libraries['drupal.dialog.off_canvas']['css']['base']['misc/dialog/off-canvas.reset.css']);
      }
    }
  }

}
