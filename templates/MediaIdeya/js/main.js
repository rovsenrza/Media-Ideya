(function () {
  'use strict';

  document.documentElement.classList.add('mi-js');

  window.MI = window.MI || {
    on: function (event, handler) {
      document.addEventListener(event, handler);
    },
  };

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function clampHand(v, a, b) {
    return Math.min(b, Math.max(a, v));
  }

  /* Footer — AOS enter + scroll-driven hand nudge (bidirectional, clamped) */
  var footer = document.querySelector('.mi-footer[data-aos="footer"]');
  if (footer) {
    var cta = footer.querySelector('.mi-footer__cta');
    var handTick = false;
    var enterTimer = null;

    function markEnterDone() {
      footer.classList.add('aos-enter-done');
    }

    function ctaHandProgress() {
      if (!cta) return 0;
      var rect = cta.getBoundingClientRect();
      var vh = window.innerHeight;
      var range = Math.max(Math.min(vh * 0.42, cta.offsetHeight * 0.55), 1);
      var startTop = vh * 0.78;
      return clampHand((startTop - rect.top) / range, 0, 1);
    }

    function syncFooterHands() {
      handTick = false;
      if (!footer.classList.contains('aos-animate')) return;
      footer.style.setProperty('--mi-footer-hand-p', ctaHandProgress().toFixed(4));
    }

    function onFooterScroll() {
      if (!handTick) {
        handTick = true;
        requestAnimationFrame(syncFooterHands);
      }
    }

    function startFooterHands() {
      footer.classList.add('aos-animate');
      if (reduce) {
        footer.style.setProperty('--mi-footer-hand-p', '1');
        markEnterDone();
        return;
      }
      syncFooterHands();
      window.addEventListener('scroll', onFooterScroll, { passive: true });
      window.addEventListener('resize', onFooterScroll, { passive: true });
      if (enterTimer) window.clearTimeout(enterTimer);
      enterTimer = window.setTimeout(markEnterDone, 1150);
    }

    if (reduce) {
      startFooterHands();
    } else if ('IntersectionObserver' in window) {
      var footerObs = new IntersectionObserver(
        function (entries) {
          for (var i = 0; i < entries.length; i++) {
            if (!entries[i].isIntersecting) continue;
            startFooterHands();
            footerObs.disconnect();
            break;
          }
        },
        { root: null, rootMargin: '0px 0px -8% 0px', threshold: 0.12 }
      );
      footerObs.observe(footer);
    } else {
      startFooterHands();
    }
  }
})();
