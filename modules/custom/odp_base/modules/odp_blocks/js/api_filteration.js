/**
 * @file
 * API Filteration links.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.apiFilteration = {
    attach: function attach(context, settings) {
      jQuery( "#edit-environment" ).change(function() {
        var environment_id = this.value;
        var parent_id = $('.parent_id').val();
        jQuery.ajax({
          url: Drupal.url('api-name'),
          type: "POST",
          data: {environment_id: environment_id, parent_id: parent_id},
          dataType: "json",
          beforeSend: function(x) {
            if (x && x.overrideMimeType) {
              x.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(result) {
            var $el = $("#edit-title");
            $el.empty();
            $.each(result, function(key,value) {
              $el.append($("<option></option>")
                 .attr("value", key).text(value));
            });
          }
        });
      });

      jQuery( "#edit-title" ).change(function() {
        var api_id = this.value;
        jQuery.ajax({
          url: Drupal.url('api-version'),
          type: "POST",
          data: {api_id: api_id},
          dataType: "json",
          beforeSend: function(x) {
            if (x && x.overrideMimeType) {
              x.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(result) {
            var $el = $("#edit-version");
            $el.empty();
            $.each(result, function(key,value) {
              $el.append($("<option></option>")
                 .attr("value", key).text(value));
            });
          }
        });
      });

      jQuery( "#edit-version" ).change(function() {
        var vid = this.value;
        var nid = $('#edit-title').val();
        var parent_id = $('.parent_id').val();
        window.location = window.location.origin + '/node/' + nid + '/revisions/' + vid + '/view?parent=' + parent_id;
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
