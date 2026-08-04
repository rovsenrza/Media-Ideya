(function () {
  'use strict';

  document.documentElement.classList.add('mi-js');

  window.MI = window.MI || {
    on: function (event, handler) {
      document.addEventListener(event, handler);
    },
  };

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Footer / onfooter — AOS for hands/copy; watermark scrubbed by scroll */
  var footer = document.querySelector('.mi-footer[data-aos="footer"]');
  if (footer) {
    function clamp(n, a, b) {
      return Math.min(b, Math.max(a, n));
    }

    function syncBrand() {
      var bar = footer.querySelector('.mi-footer__bar');
      if (!bar) return;
      var rect = bar.getBoundingClientRect();
      var vh = window.innerHeight || 1;
      /* Hold until bar is well into view; finish near end of footer bar */
      if (rect.top > vh * 0.88) {
        footer.style.setProperty('--mi-footer-brand-p', '0');
        return;
      }
      var range = Math.max(rect.height + vh * 0.45, 1);
      var p = clamp((vh - rect.top) / range, 0, 1);
      footer.style.setProperty('--mi-footer-brand-p', p.toFixed(4));
    }

    if (reduce) {
      footer.classList.add('aos-animate');
      footer.style.setProperty('--mi-footer-brand-p', '1');
    } else {
      var brandTick = false;
      function onBrandScroll() {
        if (!brandTick) {
          brandTick = true;
          requestAnimationFrame(function () {
            brandTick = false;
            syncBrand();
          });
        }
      }
      window.addEventListener('scroll', onBrandScroll, { passive: true });
      window.addEventListener('resize', onBrandScroll, { passive: true });
      syncBrand();

      if ('IntersectionObserver' in window) {
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
  }
})();
