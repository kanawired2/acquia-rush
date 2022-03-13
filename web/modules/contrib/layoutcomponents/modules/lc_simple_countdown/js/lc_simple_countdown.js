/**
 * @file
 * A JavaScript file for lc_simple_countdown.
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.LayoutComponentsSimpleCountdown = {
    attach: function (context, settings) {
      $('*[id^="lc-simple_countdown"]').each(function () {
        let id = $(this).attr('id');
        let date = $(this).attr('countdown');
        if (id && date) {
          let element = $('#' + id);
          let countDownDate = new Date(date).getTime();
          let x = setInterval(function () {
            let now = new Date().getTime();
            let distance = countDownDate - now;
            let days = Math.floor(distance / (1000 * 60 * 60 * 24));
            let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);
            // Display the result in the element with id="demo"
            element.html(days + "d " + hours + "h " + minutes + "m " + seconds + "s");
            // If the count down is finished, write some text
            if (distance < 0) {
              clearInterval(x);
              element.innerHTML = "0 d 0 h 0 m 0 s";
            }
          }, 1000);
        }
      });
    }
  };
})(jQuery, Drupal);
