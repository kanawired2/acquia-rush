/**
 * @file
 * Provides Simple Home Caoursel integration.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Attaches slick home carousel to change the dot values.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.lcHomeCarrousel = {
    attach: function (context) {
      $(document).ready(function () {
        if (!Drupal.isEmpty(drupalSettings.homeCarousel)) {
          $.each(drupalSettings.homeCarousel.items, function (e) {
            var $slick = $('.' + drupalSettings.homeCarousel.items[e].id).find('.slick__slider');
            var $dots = $slick.find('ul').addClass('slick-dots-tabs').find('li[role="presentation"]');
            var $isPaused = ($slick.hasClass('is-paused')) ? Drupal.t('Play') : Drupal.t('Pause');
            var $li = $('<li class="slick-dot-control slick-play-active">' + $isPaused + '</li>').on('click', function () {
              // Movement control.
              if ($slick.hasClass('is-paused')) {
                $(this).text(Drupal.t('Pause'));
                $slick.removeClass('is-paused').slick('slickPlay');
                $li.addClass('slick-play-active');
                $li.removeClass('slick-play-pause');
              } else {
                $(this).text(Drupal.t('Play'));
                $slick.addClass('is-paused').slick('slickPause');
                $li.removeClass('slick-play-active');
                $li.addClass('slick-play-pause');
              }
            });

            if (!Drupal.isEmpty($dots)) {
              if (!Drupal.isEmpty(drupalSettings.homeCarousel.items[e].items)) {
                $dots.each(function (i) {
                  var $text = '<div class="slick-dot-number">' + (i + 1) + ' </div>';
                  var title = (!Drupal.isEmpty(drupalSettings.homeCarousel.items[e].items[i])) ? drupalSettings.homeCarousel.items[e].items[i].title : '';
                  $text += '<div class="slick-dot-text">' + title + '</div>';
                  $($dots[i]).find('button').html($text);
                });
                $slick.find('ul').once('added').append($li);
              } else {
                var $ul = $('<ul class="slick-dots slick-dots-tabs slick-button-controls" role="tablist"/>');
                $slick.once('added').append($ul.append($li));
              }
            }
          });
        }
      })
    }
  };
})(jQuery, Drupal, drupalSettings);
