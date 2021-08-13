/**
 * @file
 * Defines Javascript behaviors for the cookies module.
 */
(function (Drupal, $) {
  'use strict';

  /**
   * Define defaults.
   */
  Drupal.behaviors.cookiesVideoEmbedField = {
    activate: function (context) {
      $('iframe.cookies-video-embed-field', context).each(function (i, element) {
        var $element = $(element);
        if ($element.attr('src') !== $element.data('src')) {
          $element.attr('src', $element.data('src'));
        }
      });
    },

    fallback: function (context) {
      $('iframe.cookies-video-embed-field', context).cookiesOverlay('video');
    },

    attach: function (context) {
      var self = this;
      document.addEventListener('cookiesjsrUserConsent', function(event) {
        var service = (typeof event.detail.services === 'object') ? event.detail.services : {};
        if (typeof service.video !== 'undefined' && service.video) {
          self.activate(context);
        } else {
          self.fallback(context);
        }
      });
    }
  };

})(Drupal, jQuery);
