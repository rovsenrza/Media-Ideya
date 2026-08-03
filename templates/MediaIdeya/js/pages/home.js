(function () {
  'use strict';

  var scrollBtn = document.querySelector('.mi-hero__scroll');
  if (!scrollBtn) return;

  scrollBtn.addEventListener('click', function (e) {
    var target = document.querySelector(scrollBtn.getAttribute('href'));
    if (!target) return;
    e.preventDefault();
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
})();
