(function () {
  "use strict";

  var page = document.querySelector("[data-services-page]");

  if (!page) {
    return;
  }

  var reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  var revealItems = Array.prototype.slice.call(
    page.querySelectorAll("[data-service-reveal]")
  );

  page.classList.add("mi-service-detail--js");

  revealItems.forEach(function (item, index) {
    item.style.transitionDelay = Math.min(index * 45, 180) + "ms";
  });

  if (reducedMotion.matches || !("IntersectionObserver" in window)) {
    revealItems.forEach(function (item) {
      item.classList.add("is-visible");
    });
  } else {
    var revealObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }

          entry.target.classList.add("is-visible");
          revealObserver.unobserve(entry.target);
        });
      },
      {
        rootMargin: "0px 0px -8%",
        threshold: 0.12
      }
    );

    revealItems.forEach(function (item) {
      revealObserver.observe(item);
    });
  }

  var requestSection = page.querySelector("#service-request");
  var requestLink = page.querySelector('a[href="#service-request"]');

  if (requestSection && requestLink) {
    requestLink.addEventListener("click", function (event) {
      event.preventDefault();
      requestSection.scrollIntoView({
        behavior: reducedMotion.matches ? "auto" : "smooth",
        block: "start"
      });
    });
  }

  var channelButtons = Array.prototype.slice.call(
    page.querySelectorAll("[data-service-channel]")
  );
  var channelInput = page.querySelector("[data-service-channel-input]");

  channelButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      var isSelected = button.classList.contains("is-selected");

      channelButtons.forEach(function (item) {
        item.classList.remove("is-selected");
        item.setAttribute("aria-pressed", "false");
      });

      if (isSelected) {
        if (channelInput) {
          channelInput.value = "";
        }
        return;
      }

      button.classList.add("is-selected");
      button.setAttribute("aria-pressed", "true");

      if (channelInput) {
        channelInput.value = button.getAttribute("data-service-channel") || "";
      }
    });
  });

  var requestForm = page.querySelector("[data-service-form]");

  if (requestForm) {
    requestForm.addEventListener("submit", function (event) {
      event.preventDefault();
      if (!requestForm.reportValidity()) return;

      var submit = requestForm.querySelector('[type="submit"]');
      var phone = requestForm.querySelector('[name="phone"]');
      var message = requestForm.querySelector('[name="message"]');
      var selectedChannel = requestForm.querySelector('[name="contact_channel"]');
      var originalLabel = submit ? submit.textContent : "";

      if (submit) {
        submit.disabled = true;
        submit.textContent = "Отправляем…";
      }

      fetch(requestForm.action, { credentials: "same-origin" })
        .then(function (response) {
          if (!response.ok) throw new Error("feedback-page");
          return response.text();
        })
        .then(function (html) {
          var feedbackPage = new DOMParser().parseFromString(html, "text/html");
          var recipient = feedbackPage.querySelector('[name="recip"]');
          var challenge = feedbackPage.querySelector(
            '.g-recaptcha, .h-captcha, .cf-turnstile, .smart-captcha, [name="sec_code"], [name="question_answer"]'
          );

          if (!recipient || challenge) throw new Error("feedback-challenge");

          var payload = new FormData();
          payload.append("send", "send");
          payload.append("skin", typeof dle_skin === "string" ? dle_skin : "MediaIdeya");
          payload.append("user_hash", typeof dle_login_hash === "string" ? dle_login_hash : "");
          payload.append("recip", recipient.value);
          payload.append("name", requestForm.elements.name.value);
          payload.append("mail", requestForm.elements.mail.value);
          payload.append("subject", "Заявка: Product-placement");
          payload.append(
            "message",
            [
              "Телефон: " + phone.value,
              selectedChannel && selectedChannel.value ? "Удобный канал: " + selectedChannel.value : "",
              message.value || "Описание задачи не указано."
            ].filter(Boolean).join("\n")
          );

          var root = typeof dle_root === "string" ? dle_root : requestForm.action;
          return fetch(root + "index.php?controller=ajax&mod=feedback", {
            method: "POST",
            body: payload,
            credentials: "same-origin"
          });
        })
        .then(function (response) {
          if (!response.ok) throw new Error("feedback-submit");
          return response.json();
        })
        .then(function (result) {
          if (!result || result.status !== "ok") {
            throw new Error(result && result.text ? result.text : "feedback-rejected");
          }

          requestForm.classList.add("is-sent");
          requestForm.innerHTML = '<p class="mi-service-detail__form-success" role="status">Спасибо! Заявка отправлена.</p>';
        })
        .catch(function () {
          try {
            sessionStorage.setItem(
              "mi-feedback-draft",
              JSON.stringify({
                name: requestForm.elements.name.value,
                mail: requestForm.elements.mail.value,
                phone: phone.value,
                message: message.value
              })
            );
          } catch (storageError) {
            /* The feedback page still works when storage is unavailable. */
          }

          window.location.assign(requestForm.action);
        })
        .finally(function () {
          if (submit && document.contains(submit)) {
            submit.disabled = false;
            submit.textContent = originalLabel;
          }
        });
    });
  }

  var finePointer = window.matchMedia("(hover: hover) and (pointer: fine)");
  var stage = page.querySelector(".mi-service-detail__stage");
  var bubbles = Array.prototype.slice.call(
    page.querySelectorAll("[data-service-bubble]")
  );

  if (!reducedMotion.matches && finePointer.matches && stage && bubbles.length) {
    var pointerX = 0;
    var pointerY = 0;
    var animationFrame = 0;

    var renderBubbleShift = function () {
      animationFrame = 0;

      bubbles.forEach(function (bubble, index) {
        var depth = 7 + index * 1.5;
        var direction = index % 2 === 0 ? 1 : -1;

        bubble.style.setProperty(
          "--mi-bubble-x",
          pointerX * depth * direction + "px"
        );
        bubble.style.setProperty(
          "--mi-bubble-y",
          pointerY * depth + "px"
        );
      });
    };

    stage.addEventListener("pointermove", function (event) {
      var bounds = stage.getBoundingClientRect();

      pointerX = Math.max(
        -1,
        Math.min(1, (event.clientX - bounds.left - bounds.width / 2) / (bounds.width / 2))
      );
      pointerY = Math.max(
        -1,
        Math.min(1, (event.clientY - bounds.top - bounds.height / 2) / (bounds.height / 2))
      );

      if (!animationFrame) {
        animationFrame = window.requestAnimationFrame(renderBubbleShift);
      }
    });

    stage.addEventListener("pointerleave", function () {
      pointerX = 0;
      pointerY = 0;

      if (!animationFrame) {
        animationFrame = window.requestAnimationFrame(renderBubbleShift);
      }
    });
  }
})();
