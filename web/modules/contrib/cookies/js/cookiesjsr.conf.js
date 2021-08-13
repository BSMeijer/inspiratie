/**
 * @file
 * Defines Javascript behaviors for the cookies module.
 */

(function (Drupal, drupalSettings) {
  'use strict';

  document.cookiesjsr = drupalSettings.cookiesjsr;

  /**
   * Define defaults.
   */
  Drupal.behaviors.cookiesjsr = {
    attach: function() {
      document.addEventListener('cookiesjsrCallbackResponse', function (event) {
        var response = (typeof event.detail.response === 'object') ? event.detail.response : {};
        const messages = new Drupal.Message();
        messages.clear();
        for (let module in response) {
          let obj = response[module];
          for (let type of ['status', 'warning', 'error']) {
            if (typeof obj[type] !== 'undefined') {
              messages.add(obj[type], {type: type});
            }
          }
        }
      });
    }
  }
})(Drupal, drupalSettings);
