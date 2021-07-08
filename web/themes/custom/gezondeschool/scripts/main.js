(function ($) {
  'use strict';
  Drupal.behaviors.labelsAsPlaceholders = {
    attach: function (context, settings) {
      $('label').each(function () {
        var input = $(this).siblings('input');
        input.attr('placeholder', $(this).html());
      });
    }
  };

  Drupal.behaviors.headerFixer = {
    attach: function (context, settings) {
      $(window).scroll(function () {
        if ($(this).scrollTop() > 0) {
          $('body').addClass('scrolled');
        }
        else {
          $('body').removeClass('scrolled');
        }
      });
    }
  };

  Drupal.behaviors.storySlick = {
    attach: function (context, settings) {

      $('.paragraph--type--stories .field--name-field-stories', context).slick({
        infinite: true,
        centerMode: false,
        slidesToShow: 4,
        slidesToScroll: 1,
        prevArrow: "<button class='slick-prev slick-arrow' aria-label='Vorige' type='button'>&lsaquo;</button>",
        nextArrow: "<button class='slick-next slick-arrow' aria-label='Volgende' type='button'>&rsaquo;</button>",
        dots: true,
        responsive: [
          {
            breakpoint: 1024,
            settings: {
              slidesToShow: 3
            },
          },
          {
            breakpoint: 768,
            settings: {
              slidesToShow: 2,
            }
          },
          {
            breakpoint: 600,
            settings: {
              slidesToShow: 1,
            }
          },
        ]
      });
    }
  }

  Drupal.behaviors.jsActivation = {
    attach: function (context, settings) {
      $('body').once().addClass('js-enabled');
    }
  }

  Drupal.behaviors.searchBar = {
    attach: function (context, settings) {
      $('.search-toggler', context).click(function (context, settings) {
        if ($('body').hasClass('search-open')) {
          $('body').removeClass('search-open');
        }
        else {
          $('body').addClass('search-open');
        }
        return false;
      });
    }
  };

  Drupal.behaviors.mobile_search = {
    attach: function (context, settings) {
      $('.mobile-search-target-wrapper', context).append($('.navigation-wrapper .block-views-exposed-filter-blocksearch-page-1').clone());
    }
  }

  Drupal.behaviors.mobile_menu = {
    attach: function (context, settings) {
      $('.mobile-menu-target-wrapper', context).append($('.menu--main > ul.menu').clone());

      $('.menu-toggler', context).click(function () {
        if ($('body').hasClass('menu-open')) {
          $('body').removeClass('menu-open');
        }
        else {
          $('body').addClass('menu-open');
        }
      });

      $('.closer').click(function () {
        $('body').removeClass('menu-open');
        return false;
      });

      $('.menu-blinder').click(function () {
        $('body').removeClass('menu-open');
        return false;
      });
    }
  };
})(jQuery);