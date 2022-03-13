<?php

namespace Drupal\layoutcomponents;

use Drupal\layout_builder\SectionStorageInterface;

/**
 * Class LcSectionManager.
 */
class LcSectionManager {

  /**
   * Get the layout settings of a section.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage object.
   * @param $delta
   *   The section delta.
   *
   * @return array
   *   The layout settings.
   */
  public function getLayoutSettings(SectionStorageInterface $section_storage, $delta) {
    $settings = $section_storage->getSection($delta)->getLayoutSettings();
    $settings['delta'] = $delta;
    return $settings;
  }

  /**
   * Get the id of a sub section.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage object.
   * @param $delta
   *   The section delta.
   *
   * @return string|empty
   *   The sub section id.
   */
  public function getLcId(SectionStorageInterface $section_storage, $delta) {
    $settings = $this->getLayoutSettings($section_storage, $delta);
    if (array_key_exists('sub_section', $settings)) {
      return $settings['sub_section']['lc_id'];
    }

    return '';
  }

  /**
   * Check if is a sub section.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage object.
   * @param $delta
   *   The section delta.
   *
   * @return bool
   *   If is a sub section.
   */
  public function isSubSection(SectionStorageInterface $section_storage, $delta) {
    $settings = $this->getLayoutSettings($section_storage, $delta);
    if (array_key_exists('sub_section', $settings)) {
      return TRUE;
    }
    return FALSE;
  }

}
