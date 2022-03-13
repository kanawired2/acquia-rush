<?php

namespace Drupal\layoutcomponents;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Layout\LayoutPluginManager;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * General class for Theme hooks.
 */
class LcTheme implements ContainerInjectionInterface{

  /**
   * The Layout Plugin Manager object.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManager
   */
  protected $layoutPluginManager;

  /**
   * The Request object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(LayoutPluginManager $layout_plugin_manager, RouteMatchInterface $route_match) {
    $this->layoutPluginManager = $layout_plugin_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.core.layout'),
      $container->get('current_route_match')
    );
  }

  /**
   * Implements hook_theme() for LC pages.
   *
   * @see \hook_theme()
   */
  public function theme() {
    return [
      'layoutcomponents_block_content' => [
        'render element' => 'elements',
      ],
      'layout__layoutcomponents_slick_region' => [
        'variables' => [
          'content' => NULL,
        ],
        'render element' => 'elements',
      ],
      'layout__layoutcomponents_region' => [
        'variables' => [
          'region' => NULL,
          'key' => NULL,
        ],
      ],
      'layout__layoutcomponents_subregion' => [
        'variables' => [
          'subregion' => NULL,
          'content' => NULL,
        ],
      ],
    ];
  }

  /**
   * Implements hook_theme_suggestions_HOOK() for LC sections.
   *
   * @see \hook_theme_suggestions_HOOK()
   */
  public function themeSuggestionsLayoutLayoutcomponentsBase(array &$suggestions, array $variables, $hook) {
    if ($hook == 'layout__layoutcomponents_base') {
      $classes = $variables['content']['#settings']['section']['styles']['misc']['extra_class'];
      $class = explode(',', $classes);
      if (is_array($class)) {
        $class = $class[0];
      }

      /** @var \Drupal\Core\Layout\LayoutDefinition $layout */
      $layout = $variables['content']['#layout'];

      $suggestions[] = 'layout__layoutcomponents_base__' . $layout->id();

      $node = $this->routeMatch->getParameter('node');
      if (isset($node)) {
        $suggestions[] = 'layout__layoutcomponents_base__' . (isset($class) ? ($class . '_') : '') . $layout->id() . '_' . $node->getType();
        $suggestions[] = 'layout__layoutcomponents_base__' . (isset($class) ? ($class . '_') : '') . $layout->id() . '_' . $node->id() . '_' . $node->getType();
      }
    }
  }

  /**
   * Implements hook_theme_suggestions_HOOK() for LC sections.
   *
   * @see \hook_theme_suggestions_HOOK()
   */
  public function themeSuggestionsLayoutLayoutcomponentsRegion(array &$suggestions, array $variables, $hook)
  {
    if ($hook == 'layout__layoutcomponents_region') {
      $classes = $variables['region']['styles']['misc']['extra_class'];
      $class = explode(',', $classes);
      if (is_array($class)) {
        $class = $class[0];
      }
      $suggestions[] = 'layout__layoutcomponents_region__' . (isset($class) ? (str_replace('-', '_', $class) . '_') : '') . $variables['key'];
      $node = $this->routeMatch->getParameter('node');
      if (isset($node)) {
        $suggestions[] = 'layout__layoutcomponents_region__' . (isset($class) ? (str_replace('-', '_', $class) . '_') : '') . $variables['key'] . '_' . (str_replace('-', '_', $node->getType()));
        $suggestions[] = 'layout__layoutcomponents_region__' . (isset($class) ? (str_replace('-', '_', $class) . '_') : '') . $variables['key'] . '_' . $node->id() . '__' . (str_replace('-', '_', $node->getType()));
      }
    }
  }

  /**
   * Implements hook_theme_suggestions_HOOK() for LC blocks.
   *
   * @see \hook_theme_suggestions_HOOK()
   */
  public function themeSuggestionsLayoutcomponentsBlockContent(array $variables) {
    $suggestions = [];
    $block_content = $variables['elements']['#block_content'];
    $suggestions[] = 'layoutcomponents_block_content__' . $block_content->bundle();
    $suggestions[] = 'layoutcomponents_block_content__' . $block_content->id();

    return $suggestions;
  }

  /**
   * Preprocess function for block content template.
   */
  public function preprocessLayoutcomponentsBlockContent(&$variables) {
    $variables['content'] = $variables['elements'];
    // Set configurations.
    $block_content = $variables['elements']['#block_content'];
    $variables['plugin_id'] = 'inline-block' . $block_content->bundle();
    $variables['configuration'] = [
      'provider' => 'layout-builder',
    ];
  }

  /**
   * Implements hook_theme_registry_alter() for LC pages.
   *
   * @see \hook_theme_registry_alter()
   */
  public function themeRegistryAlter(&$theme_registry) {
    if (!\Drupal::hasService('plugin.manager.core.layout')) {
      return;
    }

    // Find all Layoutcomponents Layouts.
    $layouts = $this->layoutPluginManager->getDefinitions();
    $layout_theme_hooks = [];

    foreach ($layouts as $info) {
      if ($info->getClass() === 'Drupal\layoutcomponents\Plugin\Layout\LcBase') {
        $layout_theme_hooks[] = $info->getThemeHook();
      }
    }

    foreach ($theme_registry as $theme_hook => $info) {
      if ((in_array($theme_hook, $layout_theme_hooks) || (!empty($info['base hook']) && in_array($info['base hook'], $layout_theme_hooks))) ||
        str_contains($theme_registry[$theme_hook]['template'], 'layout--layoutcomponents-base--')
      ) {
        // Include file.
        $theme_registry[$theme_hook]['includes'][] = drupal_get_path('module', 'layoutcomponents') . '/layoutcomponents.theme.inc';
        // Set new preprocess function.
        $theme_registry[$theme_hook]['preprocess functions'][] = '_layoutcomponents_preprocess_layout';
        $theme_registry[$theme_hook]['base hook'] = 'layout__layoutcomponents_base';
      }
    }
  }

  /**
   * Implements hook_help() for LC pages.
   *
   * @see \hook_help()
   */
  public function help($route_name, RouteMatchInterface $route_match) {
    if ($route_match->getRouteObject()->getOption('_layout_builder')) {
      return '';
    }

    switch ($route_name) {
      // Main module help for the layoutcomponents module.
      case 'help.page.layoutcomponents':
        $output = '';
        $output .= '<h3>' . t('About') . '</h3>';
        $output .= '<p>' . t('Block type creation') . '</p>';
        return $output;

      default:
    }
  }

}
