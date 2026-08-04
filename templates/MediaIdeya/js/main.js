(function () {
  'use strict';

  document.documentElement.classList.add('mi-js');

  window.MI = window.MI || {
    on: function (event, handler) {
      document.addEventListener(event, handler);
    },
  };

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Footer / onfooter — AOS from футер.mp4 (all pages) */
  var footer = document.querySelector('.mi-footer[data-aos="footer"]');
  if (footer) {
    if (reduce) {
      footer.classList.add('aos-animate');
    } else if ('IntersectionObserver' in window) {
      var footerObs = new IntersectionObserver(
        function (entries) {
          for (var i = 0; i < entries.length; i++) {
            if (!entries[i].isIntersecting) continue;
            footer.classList.add('aos-animate');
            footerObs.disconnect();
            break;
          }
        },
        { root: null, rootMargin: '0px 0px -8% 0px', threshold: 0.12 }
      );
      footerObs.observe(footer);
    } else {
      footer.classList.add('aos-animate');
    }
  }
})();
