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
