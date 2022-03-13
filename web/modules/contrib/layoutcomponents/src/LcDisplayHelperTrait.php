<?php

namespace Drupal\layoutcomponents;

use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;
use Drupal\layout_builder\Section;

/**
 * Methods to help with section storages using LC.
 */
trait LcDisplayHelperTrait {

  /**
   * Gets revision IDs for layout sections.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage object.
   *
   * @return array
   *   The array of sections.
   */
  protected function getOrderedSections(SectionStorageInterface $section_storage) {
    $n_sections = [];

    // Content sections.
    $sections = $section_storage->getSections();

    // Reset sections.
    $section_storage->removeAllSections(FALSE);

    if ($section_storage instanceof DefaultsSectionStorage) {
      foreach ($sections as $delta => $section) {
        if (array_key_exists('section', $section->getLayoutSettings())) {
          $d_delta = $section->getLayoutSettings()['section']['general']['basic']['section_delta'];
          $this->arrayInsert($n_sections, $d_delta, $section);
        }
      }
      ksort($n_sections);
      return $n_sections;
    }

    // Default sections.
    $defaults = $section_storage->getDefaultSectionStorage()->getSections();

    foreach ($sections as $delta => $section) {
      $settings = $section->getLayoutSettings();
      if (!array_key_exists('section', $settings)) {
        continue;
      }
      $section_label = $settings['section']['general']['basic']['section_label'];
      if ($this->checkDefaultExists($defaults, $section_label)) {
        // Remplace if the section is a defualt.
        $default = $this->getDefault($defaults, $section_label);
        if (isset($default)) {
          $d_delta = $default->getLayoutSettings()['section']['general']['basic']['section_delta'];
          if ($this->isOverWriten($default)) {
            $this->arrayInsert($n_sections, $d_delta, $default);
          }
          else {
            $this->updateOverWriten($sections[$delta], FALSE);
            $this->arrayInsert($n_sections, $delta, $sections[$delta]);
          }
          unset($sections[$delta]);
          continue;
        }
      }

      $this->arrayInsert($n_sections, $delta, $sections[$delta]);
      unset($sections[$delta]);
    }

    // Store the rest of defaults.
    /** @var \Drupal\layout_builder\Section $default */
    foreach ($defaults as $delta => $default) {
      if ($default->getLayoutId() == 'layout_builder_blank') {
        continue;
      }
      $settings = $defaults[$delta]->getLayoutSettings();
      if (!empty($settings)) {
        $section_delta = isset($settings['section']['general']['basic']['section_delta']) ? $settings['section']['general']['basic']['section_delta'] : NULL;
        if ($section_delta == 0) {
          if (count($n_sections) == 0) {
            $n_sections[$section_delta] = $defaults[$delta];
            continue;
          }
          $d_delta = $section_delta;
          $this->arrayInsert($n_sections, $d_delta, $defaults[$delta]);
        }
      }
    }

    ksort($n_sections);

    return $n_sections;
  }

  /**
   * Check if the section exists on default sections.
   *
   * @param array $defaults
   *   The array.
   * @param string $label
   *   The label of default section.
   *
   * @return bool
   *   If the default exists.
   */
  public function checkDefaultExists(array $defaults, $label) {
    /** @var \Drupal\layout_builder\Section $default */
    foreach ($defaults as $delta => $default) {
      $settings = $default->getLayoutSettings();
      if (!empty($settings)) {
        $section_label = isset($settings['section']['general']['basic']['section_label']) ? $settings['section']['general']['basic']['section_label'] : NULL;
        if (!empty($section_label)) {
          if ($section_label == $label) {
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Get the default section.
   *
   * @param array $defaults
   *   The array.
   * @param string $label
   *   The label of default section.
   *
   * @return array|null
   *   The default array.
   */
  public function getDefault(array &$defaults, $label) {
    foreach ($defaults as $delta => $default) {
      $settings = $default->getLayoutSettings();
      $d_label = $settings['section']['general']['basic']['section_label'];
      if ($d_label == $label) {
        unset($defaults[$delta]);
        return $default;
      }
    }
    return NULL;
  }

  /**
   * Get if the section is setted as overwriten.
   *
   * @param \Drupal\layout_builder\Section $new
   *   The new element.
   * @return bool
   *   TRUE or FALSE.
   */
  public function isOverWriten(Section $default) {
    return boolval($default->getLayoutSettings()['section']['general']['basic']['section_overwrite']) ?: FALSE;
  }

  /**
   * Get if the section is setted as overwriten.
   *
   * @param \Drupal\layout_builder\Section $default
   *   The new element.
   * @param bool $status
   *   The new status.
   * @return \Drupal\layout_builder\Section
   *   The section.
   */
  public function updateOverWriten(Section &$default, bool $status) {
    $settings = $default->getLayoutSettings();
    $settings['section']['general']['basic']['section_overwrite'] = $status;
    $default->setLayoutSettings($settings);
    return $default;
  }

  /**
   * Insert element in array by position.
   *
   * @param array $arr
   *   The array of sections.
   * @param int $index
   *   The new position.
   * @param \Drupal\layout_builder\Section $value
   *   The new section.
   */
  function arrayInsert(&$arr, $index, $value){
    $lengh = count($arr);

    if (!$this->isOverWriten($value)) {
      for($i=0; $i<($lengh+1); $i++){
        if (!array_key_exists($i, $arr)) {
          $arr[$i] = $value;
          break;
        }
      }
      return;
    }

    for($i=$lengh; $i>$index; $i--){
      $arr[$i] = $arr[$i-1];
    }

    $arr[$index] = $value;
  }

}
