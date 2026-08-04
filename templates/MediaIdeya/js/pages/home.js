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

  /* Hero title — word by word (banner art stays static) */
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

  /* Hero sticky — swipe snap start↔end (no mid sticky rest) */
  var hero = document.querySelector('[data-hero-sticky]');
  if (hero && !reduce) {
    var lines = hero.querySelectorAll('.mi-hero__title-line');
    var ticking = false;
    var snapping = false;
    var snapRaf = 0;
    var LINE_STAGGER = 0.1;
    var LINE_SPAN = 0.22;
    var SNAP_MS = 1200;

    function clamp(n, a, b) {
      return Math.min(b, Math.max(a, n));
    }

    function range() {
      return Math.max(hero.offsetHeight - window.innerHeight, 1);
    }

    function progress() {
      return clamp(-hero.getBoundingClientRect().top / range(), 0, 1);
    }

    function heroDocTop() {
      return hero.getBoundingClientRect().top + window.pageYOffset;
    }

    function isPinned() {
      var r = hero.getBoundingClientRect();
      return r.top <= 1 && r.bottom > window.innerHeight + 1;
    }

    function applyProgress(p) {
      hero.style.setProperty('--mi-hero-p', p.toFixed(4));
      for (var i = 0; i < lines.length; i++) {
        var t = clamp((p - i * LINE_STAGGER) / LINE_SPAN, 0, 1);
        lines[i].style.setProperty('--mi-line-t', t.toFixed(4));
      }
    }

    function updateHeroScroll() {
      ticking = false;
      if (snapping) return;
      applyProgress(progress());
    }

    function onScroll() {
      if (snapping) return;
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(updateHeroScroll);
      }
    }

    /* starts moving immediately — no ease-in freeze */
    function easeOutCubic(t) {
      return 1 - Math.pow(1 - t, 3);
    }

    function snapTo(target) {
      target = target >= 0.5 ? 1 : 0;
      var startP = progress();
      var startY = window.pageYOffset;
      var top = heroDocTop();
      var endY = top + target * range();

      if (Math.abs(startP - target) < 0.01 && Math.abs(startY - endY) < 2) {
        applyProgress(target);
        snapping = false;
        return;
      }

      if (snapRaf) cancelAnimationFrame(snapRaf);
      snapping = true;

      var t0 = performance.now();

      function step(now) {
        var t = clamp((now - t0) / SNAP_MS, 0, 1);
        var e = easeOutCubic(t);
        var p = startP + (target - startP) * e;
        window.scrollTo(0, startY + (endY - startY) * e);
        applyProgress(p);

        if (t < 1) {
          snapRaf = requestAnimationFrame(step);
          return;
        }

        window.scrollTo(0, endY);
        applyProgress(target);
        snapping = false;
        snapRaf = 0;
      }

      snapRaf = requestAnimationFrame(step);
    }

    function onWheel(e) {
      var p = progress();
      var pinned = isPinned();

      if (snapping) {
        if (pinned || (p > 0.01 && p < 0.99)) e.preventDefault();
        return;
      }

      if (pinned || (p > 0.02 && p < 0.98)) {
        if (e.deltaY > 4) {
          e.preventDefault();
          snapTo(1);
        } else if (e.deltaY < -4) {
          e.preventDefault();
          snapTo(0);
        }
        return;
      }

      if (p >= 0.98 && e.deltaY < -4) {
        var bottom = hero.getBoundingClientRect().bottom;
        if (bottom > window.innerHeight * 0.4) {
          e.preventDefault();
          snapTo(0);
        }
      }
    }

    var touchY = null;
    function onTouchStart(e) {
      if (e.touches && e.touches.length) touchY = e.touches[0].clientY;
    }
    function onTouchEnd(e) {
      if (touchY == null || !e.changedTouches || !e.changedTouches.length) {
        touchY = null;
        return;
      }
      var dy = touchY - e.changedTouches[0].clientY;
      touchY = null;
      if (!isPinned() || snapping) return;
      var p = progress();
      if (dy > 28 && p < 0.99) snapTo(1);
      else if (dy < -28 && p > 0.01) snapTo(0);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    window.addEventListener('wheel', onWheel, { passive: false });
    window.addEventListener('touchstart', onTouchStart, { passive: true });
    window.addEventListener('touchend', onTouchEnd, { passive: true });
    updateHeroScroll();
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
