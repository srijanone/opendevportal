(function ($) {
  Drupal.behaviors.layoutColumnsAppendClass = {
    attach: function (context) {
      // Append the class on loading.
      if ($('.ds-2col > div div').hasClass('block-field-blocknodeapi-productfield-product-attributes')) {
        $('.block-field-blocknodeapi-productfield-product-attributes').parent().parent().addClass('support-block')
      }
    }
  }
})(jQuery);
