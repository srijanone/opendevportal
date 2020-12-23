/**
 * @file
 * API Formatter select javascript behaviours
 */
(function ($, Drupal) {
  Drupal.behaviors.apiFormatterBehavior = {
    attach: function (context, settings) {
      var currLoc = $(location).attr('href'); 
      var checkHash = currLoc.split("#");
      if (checkHash[1] == null) {
        $('.opblock-summary-path').first().trigger('click');
      }
    }
  };
})(jQuery, Drupal);
