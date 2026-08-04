(function () {
  'use strict';

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Header — AOS fade-up */
  var header = document.querySelector('.mi-header[data-aos="fade-up"]');
  if (header) {
    if (reduce) {
      header.classList.add('aos-animate');
    } else {
      requestAnimationFrame(function () {
        header.classList.add('aos-animate');
      });
    }
  }

  /* Hero title — word by word */
  var title = document.querySelector('.mi-hero__title');
  if (title) {
    var words = title.querySelectorAll('.mi-hero__word');
    for (var i = 0; i < words.length; i++) {
      words[i].style.setProperty('--mi-word-i', String(i));
    }
    if (reduce) {
      title.classList.add('is-ready');
    } else {
      requestAnimationFrame(function () {
        title.classList.add('is-ready');
      });
    }
  }

  /*
   * Hero scroll — simple:
   * 1) --mi-hero-p follows real scroll
   * 2) From section start, first down-wheel goes to section end
   * 3) Everything else = normal (site-wide slow scroll in main.js)
   */
  var hero = document.querySelector('[data-hero-sticky]');
  if (hero && !reduce) {
    var lines = hero.querySelectorAll('.mi-hero__title-line');
    var LINE_STAGGER = 0.1;
    var LINE_SPAN = 0.22;
    var TO_END_MS = 1100;
    var raf = 0;

    function clamp(n, a, b) {
      return Math.min(b, Math.max(a, n));
    }

    function range() {
      return Math.max(hero.offsetHeight - window.innerHeight, 1);
    }

    function progress() {
      return clamp(-hero.getBoundingClientRect().top / range(), 0, 1);
    }

    function docTop() {
      return hero.getBoundingClientRect().top + window.pageYOffset;
    }

    function apply(p) {
      hero.style.setProperty('--mi-hero-p', p.toFixed(4));
      for (var i = 0; i < lines.length; i++) {
        var t = clamp((p - i * LINE_STAGGER) / LINE_SPAN, 0, 1);
        lines[i].style.setProperty('--mi-line-t', t.toFixed(4));
      }
    }

    function sync() {
      apply(progress());
    }

    function scrollToY(y, ms) {
      if (raf) cancelAnimationFrame(raf);
      window.MI._scrollLock = true;

      var y0 = window.pageYOffset;
      var t0 = performance.now();

      function step(now) {
        var t = clamp((now - t0) / ms, 0, 1);
        var e = 1 - Math.pow(1 - t, 3);
        window.scrollTo(0, y0 + (y - y0) * e);
        sync();

        if (t < 1) {
          raf = requestAnimationFrame(step);
          return;
        }

        window.scrollTo(0, y);
        sync();
        raf = 0;
        window.MI._scrollLock = false;
      }

      raf = requestAnimationFrame(step);
    }

    window.addEventListener(
      'wheel',
      function (e) {
        if (e.ctrlKey || e.deltaY <= 0) return;
        if (window.MI._scrollLock) {
          e.preventDefault();
          return;
        }

        /* only from the very start of the sticky section */
        if (progress() > 0.05) return;
        if (hero.getBoundingClientRect().top > 2) return;

        e.preventDefault();
        e.stopImmediatePropagation();
        scrollToY(Math.round(docTop() + range()), TO_END_MS);
      },
      { passive: false, capture: true }
    );

    window.addEventListener('scroll', sync, { passive: true });
    window.addEventListener('resize', sync, { passive: true });
    sync();
  }

  var scrollBtn = document.querySelector('.mi-hero__scroll');
  if (scrollBtn) {
    scrollBtn.addEventListener('click', function (e) {
      var target = document.querySelector(scrollBtn.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }

  var faq = document.querySelector('[data-faq]');
  if (faq) {
    faq.addEventListener(
      'toggle',
      function (e) {
        var item = e.target;
        if (!item.open || item.tagName !== 'DETAILS') return;
        var items = faq.querySelectorAll('details');
        for (var i = 0; i < items.length; i++) {
          if (items[i] !== item) items[i].open = false;
        }
      },
      true
    );
  }
})();
