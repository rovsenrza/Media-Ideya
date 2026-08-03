(function () {
  'use strict';

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
    faq.addEventListener('toggle', function (e) {
      var item = e.target;
      if (!item.open || item.tagName !== 'DETAILS') return;
      var items = faq.querySelectorAll('details');
      for (var i = 0; i < items.length; i++) {
        if (items[i] !== item) items[i].open = false;
      }
    }, true);
  }
})();
