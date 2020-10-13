/**
 * @file
 * Voyager rendering.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.nodeFieldDocument = {
    attach: function attach(context, settings) {
      var file_url = drupalSettings.opendevx_voyager.document;
      $.getJSON(file_url, function (data) {
        GraphQLVoyager.init(document.getElementById('voyager'), {
          introspection: data
        });
      }).fail(function () {
          console.log("An error has occurred.");
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
