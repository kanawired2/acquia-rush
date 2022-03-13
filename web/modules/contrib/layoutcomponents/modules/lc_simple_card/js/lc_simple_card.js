(function ($, Drupal) {
  Drupal.behaviors.SimpleCardMasonry= {
    attach:function (context, settings) {
      $('.simple-card-grid').masonry({
        itemSelector: '.simple-card-grid-item'
      });
    }
  }
})(jQuery, Drupal);
