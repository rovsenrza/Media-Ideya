(function () {
  'use strict';

  var rail = document.querySelector('[data-articles-swiper]');
  if (!rail) return;

  var track = rail.querySelector('[data-articles-track]');
  if (!track) return;

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var dragging = false;
  var activePointer = null;
  var startX = 0;
  var startScroll = 0;
  var moved = false;

  function canScroll() {
    return track.scrollWidth > track.clientWidth + 1;
  }

  track.addEventListener(
    'wheel',
    function (event) {
      if (!canScroll()) return;
      if (Math.abs(event.deltaY) <= Math.abs(event.deltaX)) return;

      event.preventDefault();
      track.scrollLeft += event.deltaY;
    },
    { passive: false }
  );

  track.addEventListener(
    'pointerdown',
    function (event) {
      if (event.button !== 0 || event.pointerType !== 'mouse') return;
      if (!canScroll()) return;

      dragging = true;
      moved = false;
      activePointer = event.pointerId;
      startX = event.clientX;
      startScroll = track.scrollLeft;
      track.classList.add('is-dragging');
      track.setPointerCapture(activePointer);
    },
    true
  );

  track.addEventListener(
    'pointermove',
    function (event) {
      if (!dragging || event.pointerId !== activePointer) return;

      var dx = event.clientX - startX;
      if (Math.abs(dx) > 4) {
        moved = true;
        event.preventDefault();
        track.scrollLeft = startScroll - dx;
      }
    },
    { passive: false }
  );

  function endDrag(event) {
    if (!dragging || event.pointerId !== activePointer) return;

    dragging = false;
    activePointer = null;
    track.classList.remove('is-dragging');

    if (track.hasPointerCapture(event.pointerId)) {
      track.releasePointerCapture(event.pointerId);
    }

    if (moved) {
      window.requestAnimationFrame(function () {
        moved = false;
      });
    }
  }

  track.addEventListener('pointerup', endDrag);
  track.addEventListener('pointercancel', endDrag);

  track.addEventListener(
    'click',
    function (event) {
      if (!moved) return;
      event.preventDefault();
      event.stopImmediatePropagation();
    },
    true
  );

  track.addEventListener('dragstart', function (event) {
    event.preventDefault();
  });

  if (!reduce) {
    track.addEventListener('keydown', function (event) {
      var step = track.clientWidth * 0.6;
      if (event.key === 'ArrowRight') {
        event.preventDefault();
        track.scrollBy({ left: step, behavior: 'smooth' });
      } else if (event.key === 'ArrowLeft') {
        event.preventDefault();
        track.scrollBy({ left: -step, behavior: 'smooth' });
      }
    });
  }
})();
