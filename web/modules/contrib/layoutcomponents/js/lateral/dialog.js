/**
 * @file
 * Layout Components behaviors.
 */

(function ($, Drupal) {

  'use strict';

  var ajax = Drupal.ajax,
      behaviors = Drupal.behaviors;

  behaviors.lateralParallax = {
    attach: function (context) {
      function control() {
        $(window).trigger('resize.px.parallax');
      }
      setInterval(control, 500);
    }
  }

  behaviors.lateralAccordion = {
    attach: function (context) {
      let $selector = Array(
        'details[data-drupal-selector="edit-layout-settings-container-title-container-styles"]',
        'details[data-drupal-selector="edit-layout-settings-container-section-container-styles"]',
        'details[data-drupal-selector="edit-layout-settings-container-section-container-general"]',
        'details[data-drupal-selector^="edit-layout-settings-container-regions-"]',
        'details[data-drupal-selector^="edit-group-styles-"]',
      );
      let $container = $($selector);
      $container.each(function () {
        let $items = $(this).find('details.js-form-wrapper.form-wrapper');
        $items.on('click', function () {
          closeAll($items, this);
        })
      });

      function closeAll(elements, element) {
        $.each(elements, function () {
          if (this !== element) {
            $(this).removeAttr('open');
          }
        })
      }
    }
  }

})(jQuery, Drupal);
