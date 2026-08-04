(function () {
  'use strict';

  var hero = document.querySelector('.mi-hero');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (hero && !reduce) {
    requestAnimationFrame(function () {
      hero.classList.add('is-ready');
    });

    window.setTimeout(function () {
      hero.classList.add('is-settled');
    }, 1500);

    var ticking = false;
    var updateScroll = function () {
      ticking = false;
      var rect = hero.getBoundingClientRect();
      var range = Math.max(rect.height * 0.55, 1);
      var p = Math.min(1, Math.max(0, -rect.top / range));
      hero.style.setProperty('--mi-hero-scroll', p.toFixed(4));
    };

    window.addEventListener(
      'scroll',
      function () {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(updateScroll);
      },
      { passive: true }
    );

    updateScroll();
  } else if (hero) {
    hero.classList.add('is-ready', 'is-settled');
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
