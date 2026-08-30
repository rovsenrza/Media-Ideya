(function () {
  'use strict';

  document.documentElement.classList.add('mi-js');

  window.MI = window.MI || {
    on: function (event, handler) {
      document.addEventListener(event, handler);
    },
  };

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Site-wide GSAP + Lenis smooth scrolling. Page scripts receive the single
     shared Lenis instance through MI, preventing duplicate RAF loops. */
  if (!reduce && window.gsap && window.Lenis) {
    var lenis = new window.Lenis({
      lerp: 0.09,
      duration: 1.25,
      smoothWheel: true,
      syncTouch: true,
      wheelMultiplier: 0.92,
      touchMultiplier: 0.92,
    });

    window.MI.lenis = lenis;
    document.documentElement.classList.add('lenis', 'lenis-smooth');
    window.gsap.ticker.add(function (time) {
      lenis.raf(time * 1000);
    });
    window.gsap.ticker.lagSmoothing(0);
  }

  var header = document.querySelector('.mi-header[data-aos="fade-up"]');
  if (header) {
    if (reduce) {
      header.classList.add('aos-animate');
    } else {
      window.requestAnimationFrame(function () {
        header.classList.add('aos-animate');
      });
    }

    /* Shared glass behaviour: hero pages change after their banner; all other
       pages change after the first header-height of downward scroll. */
    var glassBoundary = document.querySelector(
      '[data-hero-sticky], [data-about-hero], .mi-service-detail__hero'
    );
    var glassTick = false;

    function syncHeaderGlass() {
      glassTick = false;
      var threshold = header.offsetHeight || 72;
      var shouldGlass = glassBoundary
        ? glassBoundary.getBoundingClientRect().bottom <= threshold
        : window.pageYOffset > threshold;
      header.classList.toggle('mi-header--glass', shouldGlass);
    }

    function onHeaderScroll() {
      if (!glassTick) {
        glassTick = true;
        window.requestAnimationFrame(syncHeaderGlass);
      }
    }

    window.addEventListener('scroll', onHeaderScroll, { passive: true });
    window.addEventListener('resize', onHeaderScroll, { passive: true });
    syncHeaderGlass();
  }

  var menuToggle = document.querySelector('[data-mobile-menu-toggle]');
  var mobileMenu = document.querySelector('[data-mobile-menu]');
  if (menuToggle && mobileMenu) {
    menuToggle.addEventListener('click', function () {
      var open = header.classList.toggle('is-menu-open');
      menuToggle.setAttribute('aria-expanded', String(open));
      menuToggle.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
    });

    mobileMenu.addEventListener('click', function (event) {
      if (event.target.closest('a')) {
        header.classList.remove('is-menu-open');
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-label', 'Открыть меню');
      }
    });
  }

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
