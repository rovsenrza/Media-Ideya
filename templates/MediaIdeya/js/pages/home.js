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
    var snapTarget = null;
    /* hard lock scroll Y after snap — kills trackpad momentum 2nd scroll */
    var freezeY = null;
    var freezeTimer = 0;
    var LINE_STAGGER = 0.1;
    var LINE_SPAN = 0.22;
    var SNAP_MS = 1200;
    var FREEZE_IDLE_MS = 550;

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

    function clearFreezeLater() {
      window.clearTimeout(freezeTimer);
      freezeTimer = window.setTimeout(function () {
        freezeY = null;
        snapTarget = null;
      }, FREEZE_IDLE_MS);
    }

    function armFreeze(y) {
      freezeY = y;
      clearFreezeLater();
    }

    function easeOutCubic(t) {
      return 1 - Math.pow(1 - t, 3);
    }

    function snapTo(target) {
      target = target >= 0.5 ? 1 : 0;

      if (snapping && snapTarget === target) return;

      var top = heroDocTop();
      var endY = Math.round(top + target * range());

      if (!snapping && Math.abs(progress() - target) < 0.02) {
        window.scrollTo(0, endY);
        applyProgress(target);
        snapTarget = target;
        armFreeze(endY);
        return;
      }

      var startP = progress();
      var startY = window.pageYOffset;

      if (snapRaf) cancelAnimationFrame(snapRaf);
      snapping = true;
      snapTarget = target;
      freezeY = null;
      window.clearTimeout(freezeTimer);

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
        armFreeze(endY);
      }

      snapRaf = requestAnimationFrame(step);
    }

    function onScroll() {
      /* momentum after snap → pin back to freezeY (no 2nd scroll) */
      if (freezeY != null && !snapping) {
        if (Math.abs(window.pageYOffset - freezeY) > 0.5) {
          window.scrollTo(0, freezeY);
        }
        clearFreezeLater();
        return;
      }

      if (snapping) return;

      if (!ticking) {
        ticking = true;
        requestAnimationFrame(function () {
          ticking = false;
          if (snapping || freezeY != null) return;
          applyProgress(progress());
        });
      }
    }

    function onWheel(e) {
      if (snapping || freezeY != null) {
        e.preventDefault();
        if (freezeY != null) {
          window.scrollTo(0, freezeY);
          clearFreezeLater();
        }
        return;
      }

      var p = progress();
      var pinned = isPinned();

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
      if (snapping || freezeY != null) return;
      if (!isPinned() && progress() < 0.98) return;
      var p = progress();
      if (dy > 28 && p < 0.99) snapTo(1);
      else if (dy < -28 && p > 0.01) snapTo(0);
    }

    window.addEventListener('scroll', onScroll, { passive: false });
    window.addEventListener('resize', onScroll, { passive: true });
    window.addEventListener('wheel', onWheel, { passive: false, capture: true });
    window.addEventListener('touchstart', onTouchStart, { passive: true });
    window.addEventListener('touchend', onTouchEnd, { passive: true });
    applyProgress(progress());
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
