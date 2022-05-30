/**
 * @file
 * Debug Bar.
 */

(function (Drupal, document, cookies) {

  'use strict';

  Drupal.behaviors.debugBar = {
    attach: function () {

      const debugBar = document.getElementById('debug-bar');

      if (!debugBar || debugBar.dataset.processed) {
        return;
      }

      const toggler = debugBar.querySelector('#debug-bar-toggler');
      const togglerContent = toggler.querySelector('span');
      const items = debugBar.querySelector('#debug-bar-items');

      const toggle = function () {
        items.hidden = !items.hidden
        cookies.set('debug_bar_hidden', items.hidden);

        if (items.hidden) {
          toggler.setAttribute('aria-expanded', 'false');
          toggler.classList.remove('js-debug-bar__toggler_expanded');
          toggler.title = Drupal.t('Show debug bar');
          togglerContent.innerHTML = toggler.title;
        }
        else {
          toggler.setAttribute('aria-expanded', 'true');
          toggler.classList.add('js-debug-bar__toggler_expanded');
          toggler.title = Drupal.t('Hide debug bar');
          togglerContent.innerHTML = toggler.title;
        }
      };

      if (cookies.get('debug_bar_hidden') === 'false') {
        toggle();
      }

      toggler.addEventListener('click', toggle);
      debugBar.dataset.processed = 'true';
    }
  };

})(Drupal, document, window.Cookies);
