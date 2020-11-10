/**
 * @file
 * apigee script js.
 */

(function ($, Drupal) {
  Drupal.behaviors.nodeFieldGateway = {
    attach: function attach(context, settings) {
      let option = $('#edit-field-gateway option:selected').text().toLowerCase();
      if (option == 'apigee enterprise' || option == 'apigee hybrid') {
        changeLabel('Organisation', 'Email', 'Password');
      }
      else {
        changeLabel('Region', 'Access Key', 'Secret');
      }
      $(context).find('#edit-field-gateway').once('fieldBehavior').change(function () {
        option = $('#edit-field-gateway option:selected').text().toLowerCase();
        if (option == 'apigee enterprise' || option == 'apigee hybrid') {
          changeLabel('Organisation', 'Email', 'Password');
        }
        else {
          changeLabel('Region', 'Access Key', 'Secret');
        }
      });
      function changeLabel(org, key, secret) {
        $("label[for='edit-field-region-0-value']").text(org);
        // $("#edit-field-region-0-value").val('');
        $("label[for='edit-field-client-id-0-value']").text(key);
        // $("#edit-field-client-id-0-value").val('');
        $("label[for='edit-field-client-secret-0-value']").text(secret);
        // $("#edit-field-client-secret-0-value").val('');
      }
    }
  };
})(jQuery, Drupal);
