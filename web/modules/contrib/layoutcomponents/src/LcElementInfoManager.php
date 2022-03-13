<?php

namespace Drupal\layoutcomponents;

use Drupal\Core\Render\ElementInfoManager;
use Drupal\layoutcomponents\Element\LcElement;

/**
 * LcElementManager extended to alter LayoutBuilder Element.
 */
class LcElementInfoManager extends ElementInfoManager {

  /**
   * {@inheritdoc}
   */
  protected function alterDefinitions(&$definitions) {
    parent::alterDefinitions($definitions);
    // Replace LayoutBuilder element class.
    if (isset($definitions['layout_builder'])) {
      $definitions['layout_builder']['class'] = LcElement::class;
      $definitions['layout_builder']['provider'] = 'layoutcomponents';
    }
  }

}
