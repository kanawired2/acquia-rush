/**
 * @file
 * Layout Components behaviors.
 */

(function ($, Drupal) {

  'use strict';

  var ajax = Drupal.ajax,
      behaviors = Drupal.behaviors;

  // Hidde container if is a column.
  behaviors.lateralContainerColumn = {
    attach: function (context) {
      $('.lc-container-column').each(function () {
        $(this).prev().fadeOut(1);
      });
    }
  }

})(jQuery, Drupal);
