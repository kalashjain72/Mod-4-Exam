(function ($) {
  Drupal.behaviors.customLikeButton = {
    attach: function (context, settings) {
      $('.custom-like-button', context).once('customLikeButton').click(function () {
        var $button = $(this);
        var currentLikes = parseInt($button.attr('data-likes'));
        var newLikes = currentLikes + 1;
        $button.attr('data-likes', newLikes);
        $button.text(Drupal.t('Like (@count)', {'@count': newLikes}));
      });
    }
  };
})(jQuery);
