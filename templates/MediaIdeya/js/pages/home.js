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

  /* Hero sticky scroll — lines slide up on way down, slide down on way up */
  var hero = document.querySelector('[data-hero-sticky]');
  if (hero && !reduce) {
    var lines = hero.querySelectorAll('.mi-hero__title-line');
    var ticking = false;
    var LINE_STAGGER = 0.14;
    var LINE_SPAN = 0.42;

    function clamp(n, a, b) {
      return Math.min(b, Math.max(a, n));
    }

    function smoothstep(t) {
      return t * t * (3 - 2 * t);
    }

    function updateHeroScroll() {
      ticking = false;
      var max = hero.offsetHeight - window.innerHeight;
      var p = max > 0 ? clamp(-hero.getBoundingClientRect().top / max, 0, 1) : 0;

      hero.style.setProperty('--mi-hero-p', p.toFixed(4));
      hero.classList.toggle('is-leaving', p > 0.08);

      for (var i = 0; i < lines.length; i++) {
        var start = i * LINE_STAGGER;
        var t = clamp((p - start) / LINE_SPAN, 0, 1);
        lines[i].style.setProperty('--mi-line-t', smoothstep(t).toFixed(4));
      }
    }

    function onScroll() {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(updateHeroScroll);
      }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
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
