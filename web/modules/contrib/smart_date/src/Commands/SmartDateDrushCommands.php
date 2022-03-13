<?php

namespace Drupal\smart_date\Commands;

use Drush\Commands\DrushCommands;

/**
 * A drush command file.
 *
 * @package Drupal\smart_date\Commands
 */
class SmartDateDrushCommands extends DrushCommands {

  /**
   * Drush command to migrate core fields to Smart Date fields.
   *
   * @param string $bundle
   *   Content type or other bundle whose data will be used.
   * @param string $dest
   *   Field data will be copied to.
   * @param string $source_start
   *   Field to copy data from.
   * @param string $source_end
   *   Field to copy data from.
   * @param string $source_all_day
   *   Field to copy data from.
   * @param array $options
   *   Additional configuration options.
   *
   * @command smart_date:migrate
   * @alises sdm
   * @option clear
   *   Clear any data in the destination field.
   * @option entity
   *   Which entity to use as the destination.
   * @option default_duration
   *   If no end date, assumed duration.
   * @option langcode
   *   Language code to store.
   */
  public function migrate($bundle, $dest, $source_start, $source_end = NULL, $source_all_day = NULL, array $options = NULL) {
    if (!$options) {
      $options = [
        'clear' => FALSE,
        'entity' => 'node',
        'default_duration' => 0,
        'langcode' => NULL,
      ];
    }
    // @todo Sanitize provide input.
    $entity = $options['entity'];
    $dest_table = $entity . '__' . $dest;
    $def_duration = (int) $options['default_duration'];

    $connection = \Drupal::service('database');
    if ($options['clear']) {
      $this->output()->writeln('Clearing existing values.');
      $connection->truncate($dest_table)->execute();
    }
    $this->output()->writeln('Starting date migration.');

    // Get all events.
    $events = \Drupal::entityTypeManager()->getStorage($entity)
      ->loadByProperties(['type' => $bundle]);

    $utc = new \DateTimeZone('UTC');

    foreach ($events as $event) {
      $dates = $event->get($source_start)->getValue();
      $all_day_set = [];
      if ($source_all_day) {
        $all_day_set = $event->get($source_all_day)->getValue();
      }
      $end_dates_set = [];
      if ($source_end && $source_start != $source_end) {
        $end_dates_set = $event->get($source_end)->getValue();
      }
      $fallback_langcode = $event->get($source_start)->getLangcode();
      // Hardcoded last resort langcode value of 'und'.
      if (empty($fallback_langcode)) {
        $fallback_langcode = 'und';
      }
      $langcode = $options['langcode'] ?? $fallback_langcode;
      foreach ($dates as $delta => $date) {
        $start_date = $date['value'];
        // If a field was provided to check for all day, check it.
        if ($all_day_set) {
          $all_day = $all_day_set[$delta]['value'];
        }
        else {
          $all_day = FALSE;
        }

        if (!empty($all_day)) {
          $date = new \DateTime(substr($start_date, 0, -8) . '00:00:00', $utc);
          $date = $date->format('U');

          $start_date = $date;
          $end_date = $date + 86340;
          $duration = 1439;
        }
        else {
          $start_date = new \DateTime($start_date, $utc);
          $start_date = $start_date->format('U');

          // Remove any seconds from the incoming value.
          $start_date -= $start_date % 60;

          $end_date = NULL;

          if ($end_dates_set && isset($end_dates_set[$delta]['value'])) {
            $end_date = $end_dates_set[$delta]['value'];
          }
          else {
            // Assume a datetime range, so look for the end_value.
            if (!empty($date['end_value'])) {
              $end_date = $date['end_value'];
            }
          }

          if (!empty($end_date)) {
            $end_date = new \DateTime($end_date, $utc);
            $end_date = $end_date->format('U');

            // Remove any seconds from the incoming value.
            $end_date -= $end_date % 60;

            // If valid end date, set duration. Otherwise make a new end date.
            if ($start_date < $end_date) {
              $duration = round(($end_date - $start_date) / 60);
            }
            else {
              $end_date = NULL;
            }
          }

          if (!$end_date) {
            // If the end date is bogus, use default duration.
            $end_date = $start_date + ($def_duration * 60);
            $duration = $def_duration;
          }

          // Insert the resulting data.
          $result = $connection->insert($dest_table)
            ->fields([
              'bundle' => $bundle,
              'deleted' => 0,
              'entity_id' => $event->id(),
              'revision_id' => $event->getRevisionId(),
              'langcode' => $langcode,
              'delta' => $delta,
              $dest . '_value' => $start_date,
              $dest . '_end_value' => $end_date,
              $dest . '_duration' => $duration,
              $dest . '_rrule' => NULL,
              $dest . '_rrule_index' => NULL,
              $dest . '_timezone' => '',
            ])
            ->execute();
        }
      }
    }

    $this->output()->writeln('Finished date migration, flushing caches.');
    drupal_flush_all_caches();
  }

}
