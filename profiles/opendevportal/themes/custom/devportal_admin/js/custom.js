/**
 * @file
 * Global utilities.
 */
(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.devportal_admin = {
    attach: function (context, settings) {
      $('.dropdown-toggle').on(
        'click', function () {
          $(this).parent('.menu--account').toggleClass('show')
        }
      )

      $('.navbar-toggler').on(
        'click', function () {
          var height = $('.header>nav .navbar-collapse .region-primary-menu').height()
          $(this).siblings('.navbar-collapse').toggleClass('show')

          if ($('.header>nav .navbar-collapse').hasClass('show')) {
            $('.header>nav .navbar-collapse').css('height', height + 5)
          } else {
            $('.header>nav .navbar-collapse').css('height', '0')
          }
        }
      )

      if ($('form').is('.node-apps-form, .node-apps-edit-form, .node-issues-form, .node-forum-form')) {
        $('body').addClass('center-form')
      }
    }
  }
})(jQuery, Drupal)
