(function ($, Drupal) {
  Drupal.behaviors.VideoVeil = {
    attach: function (context, settings) {
      $('.lc-video-bg', context).once('VideoVeil').each(function () {
        var vid = $(this).find('video').get(0);
        $(this).on('click', function () {
          // Remove overlay.
          $(this).addClass("no-bg");
          // Check if is a HTML video or an iframe.
          if (typeof vid === "undefined") {
            // If is iframe.
            vid = $(this).find('iframe');
            var src = vid.prop('src');
            // Remove the parameters.
            src = src.replace('autoplay=0&start=0&rel=0', '');
            // Add autoplay.
            src += '&autoplay=1&enablejsapi=1'
            vid.prop('src', '');
            // From chrome 83 is necessary apply this attribute.
            vid.attr('allow', 'autoplay');
            // Add new parameters.
            vid.prop('src', src);
          } else {
            // If is a HTML video.
            vid.play();
          }
        });
      });
    }
  };

})(jQuery, Drupal);
