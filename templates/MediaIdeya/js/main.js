(function () {
  'use strict';

  document.documentElement.classList.add('mi-js');

  window.MI = window.MI || {
    on: function (event, handler) {
      document.addEventListener(event, handler);
    },
  };

  /* Site-wide slower wheel scroll */
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;

  var SPEED = 0.4;

  window.addEventListener(
    'wheel',
    function (e) {
      if (e.ctrlKey) return;
      if (window.MI && window.MI._scrollLock) return;
      e.preventDefault();
      window.scrollBy(0, e.deltaY * SPEED);
    },
    { passive: false }
  );
})();
