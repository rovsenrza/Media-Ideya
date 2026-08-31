(function () {
  'use strict';

  var dialogs = Array.prototype.slice.call(document.querySelectorAll('[data-case-modal]'));
  var triggers = Array.prototype.slice.call(document.querySelectorAll('[data-case-open]'));
  if (!dialogs.length && !triggers.length) return;

  function pathOf(value) {
    try { return new URL(value, window.location.href).pathname.replace(/\/+$/, '') || '/'; } catch (error) { return value; }
  }

  function closeDialog(dialog) {
    if (!dialog) return;
    dialog.hidden = true;
    dialog.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('mi-case-open');
  }

  function openDialog(url) {
    var targetPath = pathOf(url);
    var dialog = dialogs.find(function (item) { return pathOf(item.getAttribute('data-case-url')) === targetPath; });
    if (!dialog && /\/keysy\/?$/.test(targetPath)) dialog = dialogs[0];
    if (!dialog) return false;
    dialogs.forEach(closeDialog);
    dialog.hidden = false;
    dialog.setAttribute('aria-hidden', 'false');
    document.body.classList.add('mi-case-open');
    var header = document.querySelector('.mi-header');
    var menuToggle = document.querySelector('[data-mobile-menu-toggle]');
    if (header) header.classList.remove('is-menu-open');
    if (menuToggle) { menuToggle.setAttribute('aria-expanded', 'false'); menuToggle.setAttribute('aria-label', 'Открыть меню'); }
    var panel = dialog.querySelector('[data-case-scroll]');
    if (panel) panel.scrollTop = 0;
    return true;
  }

  triggers.forEach(function (trigger) {
    trigger.addEventListener('click', function (event) {
      if (openDialog(trigger.href || trigger.getAttribute('href'))) event.preventDefault();
    });
  });

  dialogs.forEach(function (dialog) {
    var panel = dialog.querySelector('[data-case-scroll]');
    if (panel) panel.addEventListener('wheel', function (event) { event.stopPropagation(); }, { passive: true });
    var close = dialog.querySelector('[data-case-close]');
    if (close) close.addEventListener('click', function (event) { event.preventDefault(); closeDialog(dialog); });
    dialog.addEventListener('click', function (event) { if (event.target === dialog) closeDialog(dialog); });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    dialogs.forEach(function (dialog) { if (!dialog.hidden) closeDialog(dialog); });
  });
})();
