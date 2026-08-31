(function () {
  'use strict';

  var panel = document.querySelector('[data-case-scroll]');
  if (!panel) return;

  /* The case panel scrolls only in response to the visitor's input. */
  panel.addEventListener('wheel', function (event) {
    event.stopPropagation();
  }, { passive: true });

  var close = document.querySelector('[data-case-close]');
  if (close) {
    close.addEventListener('click', function (event) {
      var referrerIsLocal = false;
      try {
        referrerIsLocal = document.referrer && new URL(document.referrer).origin === window.location.origin;
      } catch (urlError) {
        referrerIsLocal = false;
      }

      if (referrerIsLocal && history.length > 1) {
        event.preventDefault();
        history.back();
      }
    });
  }
})();
