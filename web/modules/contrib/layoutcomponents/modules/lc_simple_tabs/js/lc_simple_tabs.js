/**
 * @file
 * A JavaScript file for lc_simple_tabs.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.LayoutcomponentsSimpleTabs = {
    attach: function (context, settings) {
      var hash = window.location.hash;
      hash && $('ul.simple-tabs a[data-target="' + hash + '"]').tab('show');

      $('.simple-tabs a').click(function (e) {
        hash = this.getAttribute('data-target')
        $(this).tab('show');
        var scrollmem = $('html,body').scrollTop();
        window.location.hash = hash;
        $('html,body').scrollTop(scrollmem);
      });
    }
  };

  Drupal.behaviors.LayoutcomponentsSimpleTabsLayoutController = {
    attach: function (context, settings) {
      $('a.simplte-tabs-edit-layout').on('click', function () {
        $('.ui-dialog-titlebar-close').click();
      });
    }
  };

})(jQuery, Drupal);
