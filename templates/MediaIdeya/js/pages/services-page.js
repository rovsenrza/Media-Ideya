(function () {
  "use strict";

  var page = document.querySelector("[data-services-page]");

  if (!page) {
    return;
  }

  var reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  var stage = page.querySelector(".mi-service-detail__stage");
  var bubbles = stage
    ? Array.prototype.filter.call(stage.children, function (child) {
        return child.hasAttribute("data-service-bubble");
      })
    : [];

  if (stage && bubbles.length && bubbles.length < 5) {
    var bubbleSources = bubbles.slice();

    while (bubbles.length < 5) {
      var bubbleClone = bubbleSources[bubbles.length % bubbleSources.length].cloneNode(true);
      var cloneLinks = bubbleClone.querySelectorAll("a, button, input, select, textarea");

      bubbleClone.setAttribute("data-service-bubble-clone", "true");
      bubbleClone.setAttribute("aria-hidden", "true");

      Array.prototype.forEach.call(cloneLinks, function (control) {
        control.setAttribute("tabindex", "-1");
      });

      stage.appendChild(bubbleClone);
      bubbles.push(bubbleClone);
    }
  }

  var mobileCasesQuery = window.matchMedia("(max-width: 560px)");
  var mobileCaseAnchors = stage
    ? {
        formats: stage.querySelector('[data-service-mobile-cases="formats"]'),
        metrics: stage.querySelector('[data-service-mobile-cases="metrics"]')
      }
    : null;

  var placeMobileCases = function () {
    if (!stage || !mobileCaseAnchors || !mobileCaseAnchors.formats || !mobileCaseAnchors.metrics) {
      return;
    }

    if (!mobileCasesQuery.matches) {
      bubbles.forEach(function (bubble) {
        stage.appendChild(bubble);
      });
      return;
    }

    var formatCases = document.createElement("div");
    var metricCases = document.createElement("div");

    formatCases.className = "mi-service-detail__mobile-cases";
    metricCases.className = "mi-service-detail__mobile-cases";
    mobileCaseAnchors.formats.replaceChildren(formatCases);
    mobileCaseAnchors.metrics.replaceChildren(metricCases);

    bubbles.forEach(function (bubble, index) {
      (index < 2 ? formatCases : metricCases).appendChild(bubble);
    });
  };

  placeMobileCases();
  mobileCasesQuery.addEventListener("change", placeMobileCases);

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

  var channelButtons = page.querySelectorAll("[data-service-channel]");
  var channelInput = page.querySelector("[data-service-channel-input]");

  if (channelButtons.length && channelInput) {
    Array.prototype.forEach.call(channelButtons, function (button) {
      button.addEventListener("click", function () {
        Array.prototype.forEach.call(channelButtons, function (item) {
          item.classList.remove("is-selected");
          item.setAttribute("aria-pressed", "false");
        });

        button.classList.add("is-selected");
        button.setAttribute("aria-pressed", "true");
        channelInput.value = button.getAttribute("data-service-channel") || "";
      });
    });
  }

  var requestForm = page.querySelector("[data-service-form]");

  if (requestForm) {
    requestForm.addEventListener("submit", function (event) {
      event.preventDefault();
      if (!requestForm.reportValidity()) return;

      var submit = requestForm.querySelector('[type="submit"]');
      var phone = requestForm.querySelector('[name="phone"]');
      var message = requestForm.querySelector('[name="message"]');
      var subject = requestForm.querySelector("[data-service-subject]");
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
          payload.append(
            "subject",
            subject && subject.value ? subject.value : "Заявка с сайта"
          );
          payload.append(
            "message",
            [
              "Телефон: " + (phone.value ? "+7 " + phone.value : ""),
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

  if (!reducedMotion.matches && stage && bubbles.length) {
    var pointerX = 0;
    var pointerY = 0;
    var scrollProgress = 0;
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
        bubble.style.setProperty(
          "--mi-bubble-scroll-y",
          (scrollProgress * (5 + index * 1.25) * direction).toFixed(2) + "px"
        );
      });
    };

    var requestBubbleRender = function () {
      if (!animationFrame) {
        animationFrame = window.requestAnimationFrame(renderBubbleShift);
      }
    };

    var syncScrollShift = function () {
      var bounds = stage.getBoundingClientRect();
      var viewportCenter = window.innerHeight * 0.5;
      var range = Math.max(window.innerHeight + bounds.height * 0.5, 1);
      scrollProgress = Math.max(-1, Math.min(1, (viewportCenter - (bounds.top + bounds.height * 0.5)) / range));
      requestBubbleRender();
    };

    if (finePointer.matches) {
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

        requestBubbleRender();
      });

      stage.addEventListener("pointerleave", function () {
        pointerX = 0;
        pointerY = 0;

        requestBubbleRender();
      });
    }

    window.addEventListener("scroll", syncScrollShift, { passive: true });
    window.addEventListener("resize", syncScrollShift, { passive: true });
    syncScrollShift();
  }
})();
