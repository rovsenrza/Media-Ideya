(function () {
  'use strict';

  document.documentElement.classList.add('mi-js');

  // Shared UI hooks — components register here
  window.MI = window.MI || {
    on: function (event, handler) {
      document.addEventListener(event, handler);
    },
  };
})();
