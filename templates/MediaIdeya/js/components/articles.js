(function () {
  'use strict';

  var rail = document.querySelector('[data-articles-swiper]');
  if (!rail || typeof window.Swiper !== 'function') return;

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Mirrors articles.css: gap = 40px * --mi-s on desktop, 10px on mobile;
     trailing offset = right gutter so the last card never touches the edge. */
  function scale() {
    return Math.min(1, window.innerWidth / 1920);
  }

  function gap() {
    return window.innerWidth <= 991 ? 10 : Math.round(40 * scale());
  }

  function offsetAfter() {
    return window.innerWidth <= 991 ? 16 : Math.round(100 * scale());
  }

  /* Cards are links: without this a mouse drag starts native link drag-and-drop
     in desktop Chrome and Swiper never receives the gesture. */
  rail.addEventListener('dragstart', function (event) {
    event.preventDefault();
  });

  var swiper = new window.Swiper(rail, {
    slidesPerView: 'auto',
    spaceBetween: gap(),
    slidesOffsetAfter: offsetAfter(),
    speed: reduce ? 0 : 500,
    grabCursor: true,
    watchOverflow: true,
    freeMode: {
      enabled: true,
      sticky: false,
      momentum: !reduce,
      momentumRatio: 0.6,
      momentumBounce: false
    },
    mousewheel: {
      forceToAxis: true,
      releaseOnEdges: true
    },
    keyboard: {
      enabled: !reduce,
      onlyInViewport: true
    },
    preventClicks: true,
    preventClicksPropagation: true,
    on: {
      resize: function (instance) {
        instance.params.spaceBetween = gap();
        instance.params.slidesOffsetAfter = offsetAfter();
        instance.update();
      }
    }
  });

  rail.mediaIdeyaSwiper = swiper;
})();
