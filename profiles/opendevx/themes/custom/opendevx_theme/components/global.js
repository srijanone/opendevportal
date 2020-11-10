/**
 * @file
 * Global utilities.
 *
 */
;(function ($, Drupal) {
  'use strict'

  // For program slider when items are more than 4.
  Drupal.behaviors.opendevx_theme_program_slider = {
    attach: function (context, settings) {
      var $list = '.org_row a, .dashboard-program a'
      if ($list.length > 4) {
        $('.org_row, .dashboard-program').slick({
          infinite: false,
          slidesToShow: 4,
          slidesToScroll: 1,
          responsive: [
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 3
              }
            },
            {
              breakpoint: 600,
              settings: {
                slidesToShow: 2
              }
            },
            {
              breakpoint: 480,
              settings: {
                slidesToShow: 1
              }
            }
          ]
        })
      }
    }
  }

  // For program switcher to disappear on scroll.
  Drupal.behaviors.opendevx_theme_program_switcher = {
    attach: function (context, settings) {
      $(window).scroll(
        function () {
          var scroll = $(window).scrollTop()
          if (scroll) {
            const removeOpen = '.dashboard-user-program-header'
            const addCollapse = '.select-program'
            $(removeOpen).removeClass('show')
            $(addCollapse).addClass('collapsed')
          }
        }
      )
    }
  }

  // Added JS to show active in left navigation.
  Drupal.behaviors.leftMenu = {
    attach: function (context, settings) {
      $(function () {
        $('.sidebar_first>.section .content p a').each(
          function () {
            var $this = $(this)
            // if the current path is like this link, make it active
            if ($this.attr('href').indexOf(location.pathname) !== -1) {
              $this.addClass('active')
            }
          }
        )
      }
      )
    }
  }

  // Added JS to show active in left navigation.
  Drupal.behaviors.AppGalleryPopUp = {
    attach: function (context, settings) {
      $(function () {
        $('.node--type-apps .ds-2col-stacked-fluid .block h2+.content .field--name-field-client-id, .node--type-apps .ds-2col-stacked-fluid .block h2+.content .field--name-field-client-secret').append('<i/>');
        $('.node--type-apps .ds-2col-stacked-fluid .block h2+.content .field__item i').click (
          function () {
            $(this).parent('.field__item').toggleClass('show-keys')
          }
        )
      }
      )
    }
  }
})(jQuery, Drupal)
