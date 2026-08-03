(function () {
  'use strict';

  function initHeroScroll() {
    var scrollBtn = document.querySelector('.mi-hero__scroll');
    if (!scrollBtn) return;

    scrollBtn.addEventListener('click', function (e) {
      var target = document.querySelector(scrollBtn.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }

  function initServicesSlider() {
    var root = document.querySelector('[data-services-slider]');
    if (!root) return;

    var slides = root.querySelectorAll('[data-services-slide]');
    var prev = root.querySelector('[data-services-prev]');
    var next = root.querySelector('[data-services-next]');
    var total = slides.length;
    var index = 0;

    function show(i) {
      index = (i + total) % total;
      for (var n = 0; n < total; n++) {
        var slide = slides[n];
        var active = n === index;
        slide.hidden = !active;
        slide.classList.toggle('is-active', active);
      }
    }

    if (prev) prev.addEventListener('click', function () { show(index - 1); });
    if (next) next.addEventListener('click', function () { show(index + 1); });

    show(0);
  }

  initHeroScroll();
  initServicesSlider();
})();
