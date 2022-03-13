<?php

namespace Drupal\smart_date\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of a duration-based formatter for 'smartdate' fields.
 *
 * This formatter renders the start time range using <time> elements, with
 * the duration, using core's formatInterval functionality.
 *
 * @FieldFormatter(
 *   id = "smartdate_duration",
 *   label = @Translation("Smart Date with Duration"),
 *   field_types = {
 *     "smartdate",
 *     "daterange"
 *   }
 * )
 */
class SmartDateDurationFormatter extends SmartDateDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'duration_separator' => ' - ',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Use the upstream settings form, which gives us a control to override the
    // timezone.
    $form = parent::settingsForm($form, $form_state);

    $form['duration_separator'] = [
      '#type' => 'text',
      '#title' => $this->t('Duration Separator'),
      '#description' => $this->t('Specify what characters should be used to separate the duration from the time.'),
      '#default_value' => $this->getSetting('duration_separator'),
    ];

    // Adjust the time_wrapper description.
    $form['time_wrapper']['#description'] = $this->t('Include an HTML5 time wrapper in the markup. Time and duration will be individually wrapped.');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->getSetting('timezone_override') === ''
      ? t('No timezone override.')
      : t('Timezone overridden to %timezone.', [
        '%timezone' => $this->getSetting('timezone_override'),
      ]);

    $summary[] = t('Smart date format: %format.', [
      '%format' => $this->getSetting('format'),
    ]);

    $summary[] = t('Duration separator: %duration_separator.', [
      '%duration_separator' => $this->getSetting('duration_separator'),
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $field_type = $this->fieldDefinition->getType();
    $elements = [];
    // @todo intelligent switching between retrieval methods.
    // Look for a defined format and use it if specified.
    $format_label = $this->getSetting('format');
    if ($format_label) {
      $entity_storage_manager = \Drupal::entityTypeManager()
        ->getStorage('smart_date_format');
      $format = $entity_storage_manager->load($format_label);
      $settings = $format->getOptions();
    }
    else {
      $settings = [
        'separator' => $this->getSetting('separator'),
        'join' => $this->getSetting('join'),
        'time_format' => $this->getSetting('time_format'),
        'time_hour_format' => $this->getSetting('time_hour_format'),
        'date_format' => $this->getSetting('date_format'),
        'date_first' => $this->getSetting('date_first'),
        'ampm_reduce' => $this->getSetting('ampm_reduce'),
        'site_time_toggle' => $this->getSetting('site_time_toggle'),
        'allday_label' => $this->getSetting('allday_label'),
      ];
    }
    $timezone_override = $this->getSetting('timezone_override') ?: NULL;
    $add_classes = $this->getSetting('add_classes');
    $time_wrapper = $this->getSetting('time_wrapper');

    $augmenters = $this->initializeAugmenters();
    if ($augmenters) {
      $this->entity = $items->getEntity();
    }

    foreach ($items as $delta => $item) {
      if ($field_type == 'smartdate') {
        $timezone = $item->timezone ? $item->timezone : $timezone_override;
        if (empty($item->value) || empty($item->end_value)) {
          continue;
        }
        $start_ts = $item->value;
        $end_ts = $item->end_value;
      }
      elseif ($field_type == 'daterange') {
        $timezone = $timezone_override;
        if (empty($item->start_date) || empty($item->end_date)) {
          continue;
        }
        $start_ts = $item->start_date->getTimestamp();
        $end_ts = $item->end_date->getTimestamp();
      }
      else {
        // Not sure how to handle anything else, so return an empty set.
        return $elements;
      }
      $elements[$delta] = static::formatSmartDate($start_ts, $start_ts, $settings, $timezone);
      $elements[$delta]['spacer'] = ['#markup' => $this->getSetting('duration_separator')];
      // @todo Include timezone in isAllDay check.
      if (static::isAllDay($start_ts, $end_ts)) {
        $duration_output = $settings['allday_label'];
        unset($elements[$delta]['start']['time']);
        unset($elements[$delta]['start']['join']);
      }
      else {
        $duration_output = \Drupal::service('date.formatter')->formatDiff($start_ts, $end_ts);
      }

      $elements[$delta]['duration'] = ['#markup' => $duration_output];
      if ($add_classes) {
        if ($elements[$delta]['start'] && $elements[$delta]['start']['date']) {
          $elements[$delta]['start']['date']['#prefix'] = '<span class="smart-date--date">';
          $elements[$delta]['start']['date']['#suffix'] = '</span>';
        }
        if ($elements[$delta]['start'] && $elements[$delta]['start']['time']) {
          $elements[$delta]['start']['time']['#prefix'] = '<span class="smart-date--time">';
          $elements[$delta]['start']['time']['#suffix'] = '</span>';
        }
        if ($elements[$delta]['start'] && $elements[$delta]['duration']) {
          $elements[$delta]['duration']['#prefix'] = '<span class="smart-date--duration">';
          $elements[$delta]['duration']['#suffix'] = '</span>';
        }
      }

      if ($time_wrapper) {
        $this->addTimeWrapper($elements[$delta], $start_ts, $end_ts, $timezone);
        // For the sake of finding differences, "fix" all day events.
        if ($this->isAllDay($start_ts, $end_ts, $timezone)) {
          $adjusted_end = $end_ts + 60;
        }
        else {
          $adjusted_end = $end_ts;
        }
        $diff = \Drupal::service('date.formatter')->formatDiff($start_ts, $adjusted_end, [
          'strict' => FALSE,
          'language' => 'en',
        ]);
        $current_contents = $elements[$delta]['duration'];
        $elements[$delta]['duration'] = [
          '#theme' => 'time',
          '#attributes' => ['datetime' => $this->formatDurationTime($diff)],
          '#text' => $current_contents,
        ];
      }

      if ($augmenters) {
        $this->augmentOutput($elements[$delta], $augmenters, $start_ts, $end_ts, $timezone, $delta);
      }

      if (!empty($item->_attributes)) {
        $elements[$delta]['#attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }
    }

    return $elements;
  }

  /**
   * Format the string to be used as the datetime value.
   *
   * @param string $string
   *   The string returned by DateFormatter::formatDiff.
   *
   * @return string
   *   The formatted duration string.
   */
  private function formatDurationTime($string) {
    if (empty($string)) {
      return '';
    }
    $abbr_string = 'P';
    $intervals = [
      'Y' => 'year',
      'D' => 'day',
      'H' => 'hour',
      'M' => 'minute',
    ];
    foreach ($intervals as $key => $match_string) {
      $pattern = '/(\d)+ ' . $match_string . '(s)?/i';
      preg_match($pattern, $string, $matches);
      if ($matches) {
        $abbr_string .= $matches[1] . $key;
      }
    }
    if (strlen($abbr_string) == 1) {
      $abbr_string = '';
    }

    return $abbr_string;
  }

}
