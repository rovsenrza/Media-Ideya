(function () {
  'use strict';

  var list = document.querySelector('[data-faq]');
  if (!list) return;

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var items = list.querySelectorAll('.mi-faq__item');

  function getBody(item) {
    return item.querySelector('.mi-faq__a');
  }

  function setOpen(item, open, animate) {
    var body = getBody(item);
    if (!body) return Promise.resolve();

    if (reduce || !animate) {
      item.open = open;
      item.classList.toggle('is-open', open);
      body.style.height = open ? 'auto' : '0';
      body.style.opacity = open ? '1' : '0';
      return Promise.resolve();
    }

    return new Promise(function (resolve) {
      if (open) {
        item.open = true;
        item.classList.add('is-open');
        body.style.height = '0';
        body.style.opacity = '0';

        requestAnimationFrame(function () {
          body.style.height = body.scrollHeight + 'px';
          body.style.opacity = '1';
        });

        function onEnd(e) {
          if (e.propertyName !== 'height') return;
          body.style.height = 'auto';
          body.removeEventListener('transitionend', onEnd);
          resolve();
        }

        body.addEventListener('transitionend', onEnd);
        return;
      }

      body.style.height = body.scrollHeight + 'px';

      requestAnimationFrame(function () {
        body.style.height = '0';
        body.style.opacity = '0';
      });

      function onEnd(e) {
        if (e.propertyName !== 'height') return;
        item.open = false;
        item.classList.remove('is-open');
        body.removeEventListener('transitionend', onEnd);
        resolve();
      }

      body.addEventListener('transitionend', onEnd);
    });
  }

  items.forEach(function (item) {
    var summary = item.querySelector('.mi-faq__q');
    if (!summary) return;

    if (item.open || item.hasAttribute('open')) {
      item.open = true;
      item.classList.add('is-open');
      var body = getBody(item);
      if (body) {
        body.style.height = 'auto';
        body.style.opacity = '1';
      }
    }

    summary.addEventListener('click', function (e) {
      e.preventDefault();
      var willOpen = !item.classList.contains('is-open');
      var closes = [];

      items.forEach(function (other) {
        if (other !== item && other.classList.contains('is-open')) {
          closes.push(setOpen(other, false, true));
        }
      });

      Promise.all(closes).then(function () {
        setOpen(item, willOpen, true);
      });
    });
  });
})();
