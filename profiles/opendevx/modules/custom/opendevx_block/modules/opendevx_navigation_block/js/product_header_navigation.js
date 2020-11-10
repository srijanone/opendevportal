/**
 * @file
 * Product header navigation links.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.productHeaderNavigation = {
    attach: function attach(context, settings) {
      var path = $(location).attr('pathname')
      $('.block-product-header-navigation-block, .content').find('.dashboard-menu-item').each(function() {
        if ($(this).find('a').attr('href') == path) {
          $(this).find('a').addClass('active');
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
