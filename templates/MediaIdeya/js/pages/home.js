(function () {
  'use strict';

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Header — AOS fade-up */
  var header = document.querySelector('.mi-header[data-aos="fade-up"]');
  if (header) {
    if (reduce) {
      header.classList.add('aos-animate');
    } else {
      requestAnimationFrame(function () {
        header.classList.add('aos-animate');
      });
    }
  }

  /* Hero title — word by word */
  var title = document.querySelector('.mi-hero__title');
  if (title) {
    var words = title.querySelectorAll('.mi-hero__word');
    for (var i = 0; i < words.length; i++) {
      words[i].style.setProperty('--mi-word-i', String(i));
    }
    if (reduce) {
      title.classList.add('is-ready');
    } else {
      requestAnimationFrame(function () {
        title.classList.add('is-ready');
      });
    }
  }

  /* Hero sticky — scrub maps to video exit (~2.9–5.8s of 360.mp4)
     p 0–0.35 title lines crop (staggered)
     p 0–1    columns drift out, statue rises, bottom fade lifts
  */
  var hero = document.querySelector('[data-hero-sticky]');
  if (hero && !reduce) {
    var lines = hero.querySelectorAll('.mi-hero__title-line');
    /* 3 lines exit in ~first 35% — matches text% drop 2900→3400ms */
    var LINE_STAGGER = 0.08;
    var LINE_SPAN = 0.16;
    var ticking = false;

    function clamp(n, a, b) {
      return Math.min(b, Math.max(a, n));
    }

    function easeOut(t) {
      t = clamp(t, 0, 1);
      return 1 - Math.pow(1 - t, 2);
    }

    function range() {
      return Math.max(hero.offsetHeight - window.innerHeight, 1);
    }

    function progress() {
      return clamp(-hero.getBoundingClientRect().top / range(), 0, 1);
    }

    function apply(p) {
      hero.style.setProperty('--mi-hero-p', p.toFixed(4));
      for (var i = 0; i < lines.length; i++) {
        var t = easeOut(clamp((p - i * LINE_STAGGER) / LINE_SPAN, 0, 1));
        lines[i].style.setProperty('--mi-line-t', t.toFixed(4));
      }
    }

    function sync() {
      ticking = false;
      apply(progress());
    }

    function onScroll() {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(sync);
      }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    sync();
  }

  /* Services pin stack — equal steps; shrink then cover; pin leaves with scroll */
  var services = document.querySelector('[data-services-stack]');
  if (services && !reduce) {
    var track = services.querySelector('.mi-services__track');
    var cards = services.querySelectorAll('.mi-service-card');
    var stackTick = false;
    var SHRINK_SHARE = 0.5;
    var n = cards.length;
    var steps = Math.max(n - 1, 1);

    function clampStack(v, a, b) {
      return Math.min(b, Math.max(a, v));
    }

    function smooth(t) {
      return t * t * (3 - 2 * t);
    }

    function syncStack() {
      stackTick = false;
      if (!track || !n) return;

      var range = Math.max(track.offsetHeight - window.innerHeight, 1);
      var p = clampStack(-track.getBoundingClientRect().top / range, 0, 1);
      var seg = p * steps;
      var i = Math.min(Math.floor(seg), steps - 1);
      var local = clampStack(seg - i, 0, 1);

      for (var j = 0; j < n; j++) {
        var scaleP = 0;
        var y = 100;

        if (j < i) {
          scaleP = 1;
          y = 0;
        } else if (j === i) {
          y = 0;
          scaleP = local <= SHRINK_SHARE ? smooth(local / SHRINK_SHARE) : 1;
        } else if (j === i + 1) {
          scaleP = 0;
          if (local <= SHRINK_SHARE) {
            y = 100;
          } else {
            y = 100 * (1 - (local - SHRINK_SHARE) / (1 - SHRINK_SHARE));
          }
        } else {
          scaleP = 0;
          y = 100;
        }

        /* last card fully in when scroll ends */
        if (p >= 0.999 && j === n - 1) {
          scaleP = 0;
          y = 0;
        }
        if (p >= 0.999 && j < n - 1) {
          scaleP = 1;
          y = 0;
        }

        cards[j].style.setProperty('--mi-stack-p', scaleP.toFixed(4));
        cards[j].style.setProperty('--mi-card-y', y.toFixed(4) + '%');
      }
    }

    function onStackScroll() {
      if (!stackTick) {
        stackTick = true;
        requestAnimationFrame(syncStack);
      }
    }

    window.addEventListener('scroll', onStackScroll, { passive: true });
    window.addEventListener('resize', onStackScroll, { passive: true });
    syncStack();
  }

  /* About — AOS: play timeline when section enters viewport */
  var about = document.querySelector('.mi-about[data-aos="about"]');
  if (about) {
    if (reduce) {
      about.classList.add('aos-animate');
    } else if ('IntersectionObserver' in window) {
      var aboutObs = new IntersectionObserver(
        function (entries) {
          for (var i = 0; i < entries.length; i++) {
            if (!entries[i].isIntersecting) continue;
            about.classList.add('aos-animate');
            aboutObs.disconnect();
            break;
          }
        },
        { root: null, rootMargin: '0px 0px -12% 0px', threshold: 0.2 }
      );
      aboutObs.observe(about);
    } else {
      about.classList.add('aos-animate');
    }
  }

  /* Clients / partners — AOS enter + sticky logo scrub inside box */
  var clients = document.querySelector('[data-clients-scroll]');
  if (clients) {
    var clientsTrack = clients.querySelector('.mi-clients__track');
    var clientsBox = clients.querySelector('.mi-clients__box');
    var clientsLogos = clients.querySelector('.mi-clients__logos');
    var clientsTick = false;

    function clampClients(v, a, b) {
      return Math.min(b, Math.max(a, v));
    }

    function measureClients() {
      if (!clientsBox || !clientsLogos || !clientsTrack) return;
      var overflow = Math.max(clientsLogos.scrollHeight - clientsBox.clientHeight, 0);
      clients.style.setProperty('--mi-clients-overflow', overflow + 'px');
      /* travel ≈ overflow feel + buffer; keep pin time for logo list */
      var travel = Math.max(overflow * 1.35, window.innerHeight * 0.9);
      clientsTrack.style.height = window.innerHeight + travel + 'px';
    }

    function syncClients() {
      clientsTick = false;
      if (!clientsTrack) return;
      var range = Math.max(clientsTrack.offsetHeight - window.innerHeight, 1);
      var p = clampClients(-clientsTrack.getBoundingClientRect().top / range, 0, 1);
      clients.style.setProperty('--mi-clients-p', p.toFixed(4));
    }

    function onClientsScroll() {
      if (!clientsTick) {
        clientsTick = true;
        requestAnimationFrame(syncClients);
      }
    }

    function onClientsResize() {
      measureClients();
      syncClients();
    }

    if (reduce) {
      clients.classList.add('aos-animate');
    } else {
      measureClients();
      window.addEventListener('scroll', onClientsScroll, { passive: true });
      window.addEventListener('resize', onClientsResize, { passive: true });
      window.addEventListener('load', onClientsResize, { passive: true });
      syncClients();

      if ('IntersectionObserver' in window) {
        var clientsObs = new IntersectionObserver(
          function (entries) {
            for (var i = 0; i < entries.length; i++) {
              if (!entries[i].isIntersecting) continue;
              clients.classList.add('aos-animate');
              clientsObs.disconnect();
              /* remeasure after rows become visible */
              setTimeout(onClientsResize, 400);
              break;
            }
          },
          { root: null, rootMargin: '0px 0px -10% 0px', threshold: 0.15 }
        );
        clientsObs.observe(clients);
      } else {
        clients.classList.add('aos-animate');
      }
    }
  }

  var scrollBtn = document.querySelector('.mi-hero__scroll');
  if (scrollBtn) {
    scrollBtn.addEventListener('click', function (e) {
      var target = document.querySelector(scrollBtn.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }

  /* FAQ — letter-by-letter headline (parallel) + items L→R fade */
  var faqSection = document.querySelector('.mi-faq[data-aos="faq"]');
  if (faqSection) {
    function splitFaqChars(el) {
      if (!el || el.dataset.split === '1') return;
      var text = el.textContent;
      el.setAttribute('aria-label', text);
      el.textContent = '';
      for (var i = 0; i < text.length; i++) {
        var span = document.createElement('span');
        span.className = 'mi-faq__char';
        span.style.setProperty('--mi-char-i', String(i));
        span.setAttribute('aria-hidden', 'true');
        span.textContent = text.charAt(i) === ' ' ? '\u00A0' : text.charAt(i);
        el.appendChild(span);
      }
      el.dataset.split = '1';
    }

    splitFaqChars(faqSection.querySelector('.mi-faq__heading'));
    splitFaqChars(faqSection.querySelector('.mi-faq__label'));

    if (reduce) {
      faqSection.classList.add('aos-animate');
    } else if ('IntersectionObserver' in window) {
      var faqObs = new IntersectionObserver(
        function (entries) {
          for (var i = 0; i < entries.length; i++) {
            if (!entries[i].isIntersecting) continue;
            faqSection.classList.add('aos-animate');
            faqObs.disconnect();
            break;
          }
        },
        { root: null, rootMargin: '0px 0px -12% 0px', threshold: 0.2 }
      );
      faqObs.observe(faqSection);
    } else {
      faqSection.classList.add('aos-animate');
    }
  }

  /* Articles — title/label fade-up + cards L→R stagger */
  var articles = document.querySelector('.mi-articles[data-aos="articles"]');
  if (articles) {
    if (reduce) {
      articles.classList.add('aos-animate');
    } else if ('IntersectionObserver' in window) {
      var articlesObs = new IntersectionObserver(
        function (entries) {
          for (var i = 0; i < entries.length; i++) {
            if (!entries[i].isIntersecting) continue;
            articles.classList.add('aos-animate');
            articlesObs.disconnect();
            break;
          }
        },
        { root: null, rootMargin: '0px 0px -12% 0px', threshold: 0.18 }
      );
      articlesObs.observe(articles);
    } else {
      articles.classList.add('aos-animate');
    }
  }

  var faq = document.querySelector('[data-faq]');
  if (faq) {
    faq.addEventListener(
      'toggle',
      function (e) {
        var item = e.target;
        if (!item.open || item.tagName !== 'DETAILS') return;
        var items = faq.querySelectorAll('details');
        for (var i = 0; i < items.length; i++) {
          if (items[i] !== item) items[i].open = false;
        }
      },
      true
    );
  }
})();
