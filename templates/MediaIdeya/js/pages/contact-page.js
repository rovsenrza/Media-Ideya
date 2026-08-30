(function () {
  'use strict';

  /* Figma feedback: a restrained wreath parallax on pointer movement/scroll. */
  var wreath = document.querySelector('.mi-contact-intro__wreath');
  var intro = document.querySelector('.mi-contact-intro__body');
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var finePointer = window.matchMedia('(pointer: fine)').matches;

  if (wreath && intro && !reduceMotion && finePointer) {
    var parallaxTick = false;
    var pointerX = 0;
    var pointerY = 0;

    function clamp(value, minimum, maximum) {
      return Math.min(maximum, Math.max(minimum, value));
    }

    function syncWreathParallax() {
      parallaxTick = false;
      var rect = intro.getBoundingClientRect();
      var scrollY = clamp(-rect.top / Math.max(window.innerHeight, 1), -1, 1) * 12;
      wreath.style.setProperty('--mi-contact-wreath-x', pointerX.toFixed(2) + 'px');
      wreath.style.setProperty('--mi-contact-wreath-y', pointerY.toFixed(2) + 'px');
      wreath.style.setProperty('--mi-contact-wreath-scroll-y', scrollY.toFixed(2) + 'px');
    }

    function requestParallaxSync() {
      if (!parallaxTick) {
        parallaxTick = true;
        window.requestAnimationFrame(syncWreathParallax);
      }
    }

    intro.addEventListener('pointermove', function (event) {
      var rect = intro.getBoundingClientRect();
      pointerX = clamp((event.clientX - rect.left) / Math.max(rect.width, 1) - 0.5, -0.5, 0.5) * 16;
      pointerY = clamp((event.clientY - rect.top) / Math.max(rect.height, 1) - 0.5, -0.5, 0.5) * 12;
      requestParallaxSync();
    });

    intro.addEventListener('pointerleave', function () {
      pointerX = 0;
      pointerY = 0;
      requestParallaxSync();
    });

    window.addEventListener('scroll', requestParallaxSync, { passive: true });
    window.addEventListener('resize', requestParallaxSync, { passive: true });
    syncWreathParallax();
  }

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
      phone.dispatchEvent(new Event('input', { bubbles: true }));
      sessionStorage.removeItem('mi-feedback-draft');
    }
  } catch (storageError) {
    /* Form remains usable when storage is disabled or contains invalid data. */
  }

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
