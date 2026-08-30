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
      /* Begin only when the dark statues occupy the viewer's screen.
         The former 20% threshold started the long transition while the CTA
         was still below the fold. */
      { root: null, rootMargin: '0px', threshold: 0.65 }
    );
    observer.observe(cta);
  } else {
    activate();
  }
})();
