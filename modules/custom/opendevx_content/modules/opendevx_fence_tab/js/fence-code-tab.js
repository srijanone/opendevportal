/**
 * @file
 * Fenced Code Tabs.
 *
 */
(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.fence_code_tabs = {
    attach: function (context) {
      $('.field--name-body pre').once().wrapAll( "<div class='fenced-code-tabs' />").each(function( index ) {
        $( this ).before( '<input name=tab-group id=tab-'+ (index + 1) + ' type=radio  class=code-tab role=tab>' );
        $('.fenced-code-tabs input').first().attr('checked', 'checked')
        $( this ).before('<label for=tab-'+ (index + 1) + ' class=code-tab-label>' + $(this).children('code').attr('class').replace('language-','') + '</label>');
      });
    },
  };

  Drupal.behaviors.fence_code_tabs_para = {
    attach: function (context) {
      $('.paragraph-products pre').once().wrapAll( "<div class='fenced-code-tabs-para' />").each(function( index ) {
        $( this ).before( '<input name=tab-group-para id=tab-para'+ (index + 1) + ' type=radio  class=code-tab-para role=tab>' );
        $('.fenced-code-tabs-para input').first().attr('checked', 'checked')
        $( this ).before('<label for=tab-para'+ (index + 1) + ' class=code-tab-para-label>' + $(this).children('code').attr('class').replace('language-','') + '</label>');
      });
    },
  };

})(jQuery, Drupal, drupalSettings);
