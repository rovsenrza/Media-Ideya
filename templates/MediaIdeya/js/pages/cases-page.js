(function () {
  'use strict';

  var panel = document.querySelector('[data-case-scroll]');
  if (!panel) return;

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var mobile = window.matchMedia('(max-width: 991px)').matches;
  var cancelled = false;
  var animationFrame = 0;
  var started = false;

  /* Measurements from popup.mp4 at 30 fps: seconds → pixels on a 900px path. */
  var samples = [
    [0.0, 0], [0.1, 0], [0.2, 3], [0.3, 8], [0.4, 15], [0.5, 26],
    [0.6, 42], [0.7, 63], [0.8, 93], [0.9, 138], [1.0, 204],
    [1.1, 297], [1.2, 404], [1.3, 494], [1.4, 563], [1.5, 616],
    [1.6, 658], [1.7, 693], [1.8, 722], [1.9, 747], [2.0, 768],
    [2.2, 802], [2.5, 841], [3.0, 878], [3.3, 890], [3.6, 899],
    [3.7, 900], [4.1, 900]
  ];

  function measuredPosition(seconds) {
    for (var i = 1; i < samples.length; i++) {
      if (seconds > samples[i][0]) continue;
      var previous = samples[i - 1];
      var next = samples[i];
      var span = next[0] - previous[0];
      var progress = span ? (seconds - previous[0]) / span : 1;
      return previous[1] + (next[1] - previous[1]) * progress;
    }
    return 900;
  }

  function cancelAutoScroll() {
    cancelled = true;
    if (animationFrame) window.cancelAnimationFrame(animationFrame);
  }

  function play() {
    if (started || reduce || mobile) return;
    started = true;
    var max = Math.max(panel.scrollHeight - panel.clientHeight, 0);
    if (max < 40) return;
    var start = performance.now();

    function frame(now) {
      if (cancelled) return;
      var elapsed = Math.min((now - start) / 1000, 4.1);
      panel.scrollTop = max * (measuredPosition(elapsed) / 900);
      if (elapsed < 4.1) animationFrame = window.requestAnimationFrame(frame);
    }

    animationFrame = window.requestAnimationFrame(frame);
  }

  ['wheel', 'touchstart', 'pointerdown', 'keydown'].forEach(function (eventName) {
    panel.addEventListener(eventName, cancelAutoScroll, { passive: true, once: true });
  });

  var close = document.querySelector('[data-case-close]');
  if (close) {
    close.addEventListener('click', function (event) {
      var referrerIsLocal = false;
      try {
        referrerIsLocal = document.referrer && new URL(document.referrer).origin === window.location.origin;
      } catch (urlError) {
        referrerIsLocal = false;
      }

      if (referrerIsLocal && history.length > 1) {
        event.preventDefault();
        history.back();
      }
    });
  }

  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      if (!entries[0].isIntersecting) return;
      observer.disconnect();
      play();
    }, { threshold: 0.45 });
    observer.observe(panel);
  } else {
    play();
  }
})();
