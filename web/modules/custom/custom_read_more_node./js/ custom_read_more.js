(function ($) {
  Drupal.behaviors.customReadMore = {
    attach: function (context, settings) {
      $('.blog-summary', context).once('custom-read-more', function () {
        var $this = $(this);
        var $fullContent = $this.next('.blog-full-content');

        $this.on('click', '.read-more', function (event) {
          event.preventDefault();
          $this.hide();
          $fullContent.show();
        });

        $fullContent.on('click', '.show-less', function (event) {
          event.preventDefault();
          $this.show();
          $fullContent.hide();
        });
      });
    }
  };
})(jQuery);
