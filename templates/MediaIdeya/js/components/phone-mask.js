(function () {
  'use strict';

  function formatRussianPhone(value) {
    var digits = String(value || '').replace(/\D/g, '');

    if (digits.charAt(0) === '7' || digits.charAt(0) === '8') {
      digits = digits.slice(1);
    }

    digits = digits.slice(0, 10);
    if (!digits) return '';

    var result = '(' + digits.slice(0, 3);
    if (digits.length >= 3) result += ')';
    if (digits.length > 3) result += ' ' + digits.slice(3, 6);
    if (digits.length > 6) result += '-' + digits.slice(6, 8);
    if (digits.length > 8) result += '-' + digits.slice(8, 10);
    return result;
  }

  function syncPhone(input) {
    input.value = formatRussianPhone(input.value);
  }

  window.MI = window.MI || {};
  window.MI.formatRussianPhone = formatRussianPhone;

  Array.prototype.forEach.call(document.querySelectorAll('[data-mi-phone-mask]'), function (input) {
    input.setAttribute('inputmode', 'tel');
    input.setAttribute('pattern', '\\(\\d{3}\\) \\d{3}-\\d{2}-\\d{2}');
    input.addEventListener('input', function () {
      syncPhone(input);
    });

    if (input.form && input.name) {
      input.form.addEventListener('formdata', function (event) {
        event.formData.set(input.name, input.value ? '+7 ' + input.value : '');
      });
    }

    syncPhone(input);
  });
})();
