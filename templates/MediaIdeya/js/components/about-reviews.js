(function () {
  'use strict';

  var slider = document.querySelector('[data-reviews-swiper]');
  if (!slider || typeof window.Swiper !== 'function') return;

  var mobile = window.matchMedia('(max-width: 991px)');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var swiper = null;

  slider.addEventListener('dragstart', function (event) {
    event.preventDefault();
  });

  /* Mirrors about-page.css: 10px gap ≥480px, 2.7778vw below. */
  function gap() {
    return window.innerWidth <= 479 ? Math.round(window.innerWidth * 0.027778) : 10;
  }

  function mount() {
    if (swiper) return;
    slider.classList.add('swiper');
    swiper = new window.Swiper(slider, {
      wrapperClass: 'mi-about-reviews__list',
      slideClass: 'mi-about-reviews__card',
      slidesPerView: 'auto',
      spaceBetween: gap(),
      speed: reduce ? 0 : 500,
      grabCursor: true,
      watchOverflow: true,
      freeMode: { enabled: true, sticky: true, momentum: !reduce },
      mousewheel: { forceToAxis: true, releaseOnEdges: true },
      keyboard: { enabled: !reduce, onlyInViewport: true },
      on: {
        resize: function (instance) {
          instance.params.spaceBetween = gap();
          instance.update();
        }
      }
    });
  }

  function unmount() {
    if (!swiper) return;
    swiper.destroy(true, true);
    swiper = null;
    slider.classList.remove('swiper');
  }

  function sync() {
    if (mobile.matches) mount();
    else unmount();
  }

  sync();
  if (typeof mobile.addEventListener === 'function') mobile.addEventListener('change', sync);
  else mobile.addListener(sync);
})();
