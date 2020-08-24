/**
 * @file
 * Global utilities.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.opendevx = {
    attach: function (context) {
      if ($('.gallery-slider-wrapper .swiper-slide').length > 1) {
        var swiper = new Swiper(
          '.gallery-slider-wrapper .swiper-container', {
            slidesPerView: 1,
            loop: true,
            autoplay: {
              delay: 2000,
              disableOnInteraction: false
            },
            pagination: {
              el: '.prev-nect-wrapper .swiper-pagination',
              clickable: true
            },
            navigation: {
              nextEl: '.prev-nect-wrapper .swiper-button-next',
              prevEl: '.prev-nect-wrapper .swiper-button-prev'
            }
          }
        )
      }

      // Search custom layout.
      $(
        function () {
          if ($(window).width() > 991) {
            $('.search-block-form.block-search-form-block .container-inline > i').click(
              function (e) {
                $('.search-block-form.block-search-form-block').toggleClass('open')
                e.preventDefault()
              }
            )
          }
        }
      )
    }
  }

  Drupal.behaviors.orgDropdown = {
    attach: function (context) {
      $('.select-org').click(
        function () {
          $(this).toggleClass('open')
          const org = '.dashboard-user-org-header, .front-user-org-header'
          $(this).siblings(org).toggleClass('open')
        }
      )

      $(window).scroll(
        function () {
          var scroll = $(window).scrollTop()
          if (scroll) {
            const removeOpen = '.dashboard-user-org-header, .select-org, .front-user-org-header'
            $(removeOpen).removeClass('open')
          }
        }
      )
    }
  }

  Drupal.behaviors.layoutColumns = {
    attach: function (context) {
      if ($('.ds-1col > div').hasClass('block-system-breadcrumb-block')) {
        $('body').addClass('no-breadcrumb')
      }

      if ($('.ds-2col > div div').hasClass('block-field-blocknodeapi-productfield-product-attributes')) {
        $('.block-field-blocknodeapi-productfield-product-attributes').parent().parent().addClass('support-block')
      }

      if ($('div').hasClass('ds-3col-equal')) {
        $('.block-system-breadcrumb-block+.block-page-title-block').detach().insertBefore('.group-right>div:first')
      }

      $('.node--type-apps .ds-2col-stacked-fluid .block h2+.content .field--name-field-client-id, .node--type-apps .ds-2col-stacked-fluid .block h2+.content .field--name-field-client-secret').append('<i/>')
      $('.node--type-apps .ds-2col-stacked-fluid .block h2+.content .field__item i').click(
        function () {
          $(this).parent('.field__item').toggleClass('show-keys')
        }
      )

      $('.block-views-blockforum-latest-qna, .block-views-blockevents-upcoming-events').wrapAll("<div class='bottom-blocks'></div>")
    }
  }

  Drupal.behaviors.faq = {
    attach: function (context) {
      var answer = '.views-field-field-answer'
      $('.view-display-id-page_1 .views-row .views-field-body').click(
        function () {
          $(this).toggleClass('open')
          $(this).siblings(answer).toggleClass('open')
        }
      )

      $('.view-display-id-faq_block .views-row .views-field-body').click(
        function () {
          $(this).toggleClass('open')
          $(this).siblings(answer).toggleClass('open')
        }
      )

      $('.view-display-id-page_faq_listing .views-row:first-child .collapse').addClass('show')
      $('.view-display-id-page_faq_listing .question').click(
        function () {
          var collapse = '.view-display-id-page_faq_listing .collapse'
          $(collapse).collapse('hide')
        }
      )
    }
  }

  Drupal.behaviors.leftMenu = {
    attach: function (context) {
      $('.ds-3col .group-left .block-product-navigation-block, .ds-3col-equal .group-left .block-product-navigation-block').prepend('<h2></h2>')
      $('.ds-3col .group-left .block-product-navigation-block>h2, .ds-3col-equal .group-left .block-product-navigation-block>h2').click(
        function () {
          $(this).parents('.group-left').toggleClass('open-menu')
        }
      )

      $(
        function () {
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

  Drupal.behaviors.tourBtn = {
    attach: function (context) {
      if ($('#tour').length) {
        $('body').addClass('tourenable')
      }
    }
  }

  Drupal.behaviors.leftMenuTwice = {
    attach: function (context) {
      if ($('.ds-2col div').hasClass('block-book')) { // this code will remove after solution.
        $('body').addClass('left-menu')
      } else {
        $('body').addClass('left-menu-sticky')
      }

      $('.left-menu .sidebar_first .product-dashboard-header').click(
        function () {
          $('.sidebar_first').toggleClass('open')
        }
      )
    }
  }

  Drupal.behaviors.tour = {
    attach: function attach(context) {
      $('body').once('tour').each(
        function () {
          var model = new Drupal.tour.models.StateModel()
          new Drupal.tour.views.ToggleTourView(
            {
              el: $(context).find('.tour-btn'),
              model: model
            }
          )

          model.on(
            'change:isActive', function (model, isActive) {
              $(document).trigger(isActive ? 'drupalTourStarted' : 'drupalTourStopped')
            }
          ).set('tour', $(context).find('ol#tour'))

          if (/tour=?/i.test(queryString)) {
            model.set('isActive', true)
          }
        }
      )
    }
  }

  // Drupal.attachBehaviors()

})(jQuery, Drupal, drupalSettings)
