<?php

namespace Drupal\layoutcomponents\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listen to route events to override panels.select_block controller.
 */
class LcRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('layout_builder.choose_block')) {
      $route->setDefaults([
        '_controller' => '\Drupal\layoutcomponents\Controller\LcChooseBlockController::build',
      ]);
    }

    if ($route = $collection->get('layout_builder.choose_inline_block')) {
      $route->setDefaults([
        '_controller' => '\Drupal\layoutcomponents\Controller\LcChooseBlockController::inlineBlockList',
      ]);
    }

    if ($route = $collection->get('layout_builder.choose_section')) {
      $route->setDefaults([
        '_controller' => '\Drupal\layoutcomponents\Controller\LcChooseSectionController::build',
      ]);
    }

    if ($route = $collection->get('layout_builder.add_block')) {
      $route->setDefaults([
        '_form' => '\Drupal\layoutcomponents\Form\LcAddBlockForm',
      ]);
    }

    if ($route = $collection->get('layout_builder.update_block')) {
      $route->setDefaults([
        '_form' => '\Drupal\layoutcomponents\Form\LcUpdateBlockForm',
      ]);
    }

    if ($route = $collection->get('layout_builder.configure_section')) {
      $route->setDefaults([
        '_title' => 'Configure section',
        '_form' => '\Drupal\layoutcomponents\Form\LcConfigureSection',
        'plugin_id' => NULL,
      ]);
    }

    if ($route = $collection->get('layout_builder.remove_section')) {
      $route->setDefaults([
        '_title' => 'Remove section',
        '_form' => '\Drupal\layoutcomponents\Form\LcRemoveSection',
      ]);
    }

    if ($route = $collection->get('layout_builder.remove_block')) {
      $route->setDefaults([
        '_title' => 'Remove section',
        '_form' => '\Drupal\layoutcomponents\Form\LcRemoveBlock',
      ]);
    }

    if ($route = $collection->get('layout_builder.move_sections_form')) {
      $route->setDefaults([
        '_title' => 'Move Sections',
        '_form' => '\Drupal\layoutcomponents\Form\LcMoveSections',
      ]);
    }
  }

}
