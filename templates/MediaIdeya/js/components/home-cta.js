(function () {
  'use strict';

  var cta = document.querySelector('[data-home-cta]');
  if (!cta) return;

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var mobile = window.matchMedia('(max-width: 991px)').matches;

  function activate() {
    cta.classList.add('is-active');
  }

  if (reduce || mobile) {
    activate();
  } else if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(
      function (entries) {
        if (!entries[0].isIntersecting) return;
        activate();
        observer.disconnect();
      },
      { root: null, rootMargin: '0px 0px -12% 0px', threshold: 0.2 }
    );
    observer.observe(cta);
  } else {
    activate();
  }
})();
