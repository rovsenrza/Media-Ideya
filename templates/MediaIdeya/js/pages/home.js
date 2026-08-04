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
    var snapTimer = 0;
    var settleTimer = 0;
    var LINE_STAGGER = 0.1;
    var LINE_SPAN = 0.22;

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

    function yForProgress(p) {
      return heroDocTop() + p * range();
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
      applyProgress(progress());
    }

    function onScroll() {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(updateHeroScroll);
      }
      if (snapping) return;
      window.clearTimeout(settleTimer);
      settleTimer = window.setTimeout(function () {
        if (snapping || !isPinned()) return;
        var p = progress();
        if (p > 0.03 && p < 0.97) {
          snapTo(p >= 0.5 ? 1 : 0);
        }
      }, 60);
    }

    function snapTo(p) {
      p = p >= 0.5 ? 1 : 0;
      var y = yForProgress(p);
      if (Math.abs(window.pageYOffset - y) < 2) {
        applyProgress(p);
        snapping = false;
        return;
      }
      snapping = true;
      window.clearTimeout(snapTimer);
      window.scrollTo({ top: y, behavior: 'smooth' });
      snapTimer = window.setTimeout(function () {
        window.scrollTo({ top: yForProgress(p) });
        applyProgress(p);
        snapping = false;
      }, 700);
    }

    function onWheel(e) {
      var p = progress();
      var pinned = isPinned();

      if (snapping) {
        if (pinned || (p > 0.01 && p < 0.99)) e.preventDefault();
        return;
      }

      /* inside sticky travel — any nudge commits to start or end */
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

      /* just finished sticky — swipe up snaps back to start */
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
      if (!isPinned()) return;
      if (snapping) return;
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
