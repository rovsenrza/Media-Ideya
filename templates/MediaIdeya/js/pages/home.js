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

  /* Hero sticky — progress follows normal native scroll only */
  var hero = document.querySelector('[data-hero-sticky]');
  if (hero && !reduce) {
    var lines = hero.querySelectorAll('.mi-hero__title-line');
    var LINE_STAGGER = 0.1;
    var LINE_SPAN = 0.22;
    var ticking = false;

    function clamp(n, a, b) {
      return Math.min(b, Math.max(a, n));
    }

    function range() {
      return Math.max(hero.offsetHeight - window.innerHeight, 1);
    }

    function progress() {
      return clamp(-hero.getBoundingClientRect().top / range(), 0, 1);
    }

    function apply(p) {
      hero.style.setProperty('--mi-hero-p', p.toFixed(4));
      for (var i = 0; i < lines.length; i++) {
        var t = clamp((p - i * LINE_STAGGER) / LINE_SPAN, 0, 1);
        lines[i].style.setProperty('--mi-line-t', t.toFixed(4));
      }
    }

    function sync() {
      ticking = false;
      apply(progress());
    }

    function onScroll() {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(sync);
      }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    sync();
  }

  /* Services z-stack — phase 1 shrink, phase 2 next card covers */
  var stack = document.querySelector('[data-services-stack]');
  if (stack && !reduce) {
    var cards = stack.querySelectorAll('.mi-service-card');
    var stackTick = false;
    /* first half of approach = shrink; second half = cover only */
    var SHRINK_SHARE = 0.45;

    function clampStack(n, a, b) {
      return Math.min(b, Math.max(a, n));
    }

    function syncStack() {
      stackTick = false;
      for (var i = 0; i < cards.length; i++) {
        var p = 0;
        if (i < cards.length - 1) {
          var curTop = cards[i].getBoundingClientRect().top;
          var nextTop = cards[i + 1].getBoundingClientRect().top;
          var travel = Math.max(cards[i].offsetHeight * 0.55 + window.innerHeight * 0.35, 1);
          var raw = clampStack(1 - (nextTop - curTop) / travel, 0, 1);
          /* finish scale/opacity before cover finishes */
          if (raw <= SHRINK_SHARE) {
            var t = raw / SHRINK_SHARE;
            p = t * t * (3 - 2 * t);
          } else {
            p = 1;
          }
        }
        cards[i].style.setProperty('--mi-stack-p', p.toFixed(4));
      }
    }

    function onStackScroll() {
      if (!stackTick) {
        stackTick = true;
        requestAnimationFrame(syncStack);
      }
    }

    window.addEventListener('scroll', onStackScroll, { passive: true });
    window.addEventListener('resize', onStackScroll, { passive: true });
    syncStack();
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
