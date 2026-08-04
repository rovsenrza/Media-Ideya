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

  /* Services pin stack — equal steps; shrink then cover; pin leaves with scroll */
  var services = document.querySelector('[data-services-stack]');
  if (services && !reduce) {
    var track = services.querySelector('.mi-services__track');
    var cards = services.querySelectorAll('.mi-service-card');
    var stackTick = false;
    var SHRINK_SHARE = 0.5;
    var n = cards.length;
    var steps = Math.max(n - 1, 1);

    function clampStack(v, a, b) {
      return Math.min(b, Math.max(a, v));
    }

    function smooth(t) {
      return t * t * (3 - 2 * t);
    }

    function syncStack() {
      stackTick = false;
      if (!track || !n) return;

      var range = Math.max(track.offsetHeight - window.innerHeight, 1);
      var p = clampStack(-track.getBoundingClientRect().top / range, 0, 1);
      var seg = p * steps;
      var i = Math.min(Math.floor(seg), steps - 1);
      var local = clampStack(seg - i, 0, 1);

      for (var j = 0; j < n; j++) {
        var scaleP = 0;
        var y = 100;

        if (j < i) {
          scaleP = 1;
          y = 0;
        } else if (j === i) {
          y = 0;
          scaleP = local <= SHRINK_SHARE ? smooth(local / SHRINK_SHARE) : 1;
        } else if (j === i + 1) {
          scaleP = 0;
          if (local <= SHRINK_SHARE) {
            y = 100;
          } else {
            y = 100 * (1 - (local - SHRINK_SHARE) / (1 - SHRINK_SHARE));
          }
        } else {
          scaleP = 0;
          y = 100;
        }

        /* last card fully in when scroll ends */
        if (p >= 0.999 && j === n - 1) {
          scaleP = 0;
          y = 0;
        }
        if (p >= 0.999 && j < n - 1) {
          scaleP = 1;
          y = 0;
        }

        cards[j].style.setProperty('--mi-stack-p', scaleP.toFixed(4));
        cards[j].style.setProperty('--mi-card-y', y.toFixed(4) + '%');
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
