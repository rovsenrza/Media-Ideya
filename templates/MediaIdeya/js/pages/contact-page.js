(function () {
  'use strict';

  var phone = document.querySelector('.mi-contact-page input[type="tel"]');
  if (!phone) return;

  try {
    var draft = JSON.parse(sessionStorage.getItem('mi-feedback-draft') || 'null');
    if (draft) {
      var name = document.querySelector('.mi-contact-page [name="name"]');
      var mail = document.querySelector('.mi-contact-page [name="mail"]');
      var message = document.querySelector('.mi-contact-page [name="message"]');
      if (name && draft.name) name.value = draft.name;
      if (mail && draft.mail) mail.value = draft.mail;
      if (message && draft.message) message.value = draft.message;
      if (draft.phone) phone.value = draft.phone;
      sessionStorage.removeItem('mi-feedback-draft');
    }
  } catch (storageError) {
    /* Form remains usable when storage is disabled or contains invalid data. */
  }

  function formatRussianPhone(value) {
    var digits = value.replace(/\D/g, '');
    if (digits.charAt(0) === '8') digits = '7' + digits.slice(1);
    if (digits.charAt(0) !== '7') digits = '7' + digits;
    digits = digits.slice(0, 11);

    var result = '+7';
    if (digits.length > 1) result += ' (' + digits.slice(1, 4);
    if (digits.length >= 4) result += ')';
    if (digits.length > 4) result += ' ' + digits.slice(4, 7);
    if (digits.length > 7) result += '-' + digits.slice(7, 9);
    if (digits.length > 9) result += '-' + digits.slice(9, 11);
    return result;
  }

  phone.addEventListener('input', function () {
    phone.value = formatRussianPhone(phone.value);
  });

  phone.addEventListener('focus', function () {
    if (!phone.value) phone.value = '+7';
  });

  var sendmail = document.getElementById('sendmail');
  var engineFields = document.querySelector('.mi-project-form__engine');
  var challenge = engineFields && engineFields.querySelector(
    '[name="sec_code"], [name="question_answer"], .g-recaptcha, .h-captcha, .cf-turnstile, .smart-captcha'
  );

  if (sendmail && challenge) {
    sendmail.addEventListener('submit', function (event) {
      if (engineFields.classList.contains('is-challenge-open')) return;
      event.preventDefault();
      event.stopImmediatePropagation();
      engineFields.classList.add('is-challenge-open');
      var field = engineFields.querySelector('input:not([type="hidden"])');
      if (field) field.focus();
    }, true);
  }
})();
