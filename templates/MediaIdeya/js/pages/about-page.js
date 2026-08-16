(function () {
  'use strict';

  var page = document.querySelector('[data-about-page]');
  if (!page) return;

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var runningAnimations = [];
  var teamTimers = [];

  function initializeTeamState() {
    var team = page.querySelector('[data-about-team]');
    var roles = team ? team.querySelectorAll('[data-team-role]') : [];

    for (var i = 0; i < roles.length; i++) {
      var state = 'hidden';
      if (i === 0) state = 'active';
      else if (i === 1) state = 'next';
      else if (i === roles.length - 1) state = 'previous';

      roles[i].setAttribute('data-state', state);
      roles[i].setAttribute('aria-hidden', i === 0 ? 'false' : 'true');
    }
  }

  initializeTeamState();

  function animate(element, keyframes, options) {
    if (!element || typeof element.animate !== 'function') return null;
    var animation = element.animate(keyframes, options);
    runningAnimations.push(animation);
    return animation;
  }

  function observeOnce(element, threshold, callback) {
    if (!element) return;

    if (!('IntersectionObserver' in window)) {
      window.requestAnimationFrame(callback);
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        for (var i = 0; i < entries.length; i++) {
          if (!entries[i].isIntersecting) continue;
          observer.disconnect();
          callback();
          break;
        }
      },
      {
        root: null,
        rootMargin: '0px 0px -8% 0px',
        threshold: threshold,
      }
    );

    observer.observe(element);
  }

  function settle(section, animations) {
    section.classList.add('is-composed');
    for (var i = 0; i < animations.length; i++) {
      if (animations[i]) animations[i].cancel();
    }
  }

  function showReducedState() {
    page.classList.add('is-reduced-motion');

    var hero = page.querySelector('[data-about-hero]');
    var process = page.querySelector('[data-about-process]');
    var team = page.querySelector('[data-about-team]');
    var roles = page.querySelectorAll('[data-team-role]');

    if (hero) hero.classList.add('is-composed');
    if (process) process.classList.add('is-composed');
    if (team) {
      team.classList.add('is-composed');
      var stage = team.querySelector('.mi-about-team__stage');
      if (stage) stage.setAttribute('aria-live', 'off');
    }

    for (var i = 0; i < roles.length; i++) {
      roles[i].setAttribute('aria-hidden', 'false');
    }
  }

  if (reduceMotion || typeof page.animate !== 'function') {
    showReducedState();
    return;
  }

  /* Hero reference: 4.63 s total — word stagger, statues, then the final zoom. */
  var hero = page.querySelector('[data-about-hero]');
  var HERO_DURATION = 4630;

  function playHero() {
    if (!hero || hero.classList.contains('is-running')) return;

    hero.classList.add('is-running');
    var heroAnimations = [];
    var words = hero.querySelectorAll('[data-about-hero-word]');
    var wordDelays = [100, 275, 450, 625, 800];
    var statueGroup = hero.querySelector('[data-about-hero-statues]');
    var leftStatue = hero.querySelector('[data-about-hero-statue="left"]');
    var centerStatue = hero.querySelector('[data-about-hero-statue="center"]');
    var rightStatue = hero.querySelector('[data-about-hero-statue="right"]');

    for (var i = 0; i < words.length; i++) {
      heroAnimations.push(
        animate(
          words[i],
          [
            {
              opacity: 0,
              transform: 'translate3d(0, 38px, 0)',
              filter: 'blur(9px)',
            },
            {
              opacity: 1,
              transform: 'translate3d(0, 0, 0)',
              filter: 'blur(0)',
            },
          ],
          {
            duration: 530,
            delay: wordDelays[i] || 0,
            easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
            fill: 'both',
          }
        )
      );

      heroAnimations.push(
        animate(
          words[i],
          [
            { opacity: 1, transform: 'translate3d(0, 0, 0)', filter: 'blur(0)' },
            { opacity: 0, transform: 'translate3d(0, -10px, 0)', filter: 'blur(4px)' },
          ],
          {
            duration: 40,
            delay: 3470 + i * 40,
            easing: 'linear',
            fill: 'forwards',
          }
        )
      );
    }

    heroAnimations.push(
      animate(
        leftStatue,
        [
          {
            opacity: 0,
            transform: 'translate3d(-112px, 74px, 0) scale(0.92)',
            filter: 'blur(5px)',
          },
          {
            opacity: 1,
            transform: 'translate3d(0, 0, 0) scale(1)',
            filter: 'blur(0)',
          },
        ],
        {
          duration: 800,
          delay: 2200,
          easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
          fill: 'both',
        }
      )
    );

    heroAnimations.push(
      animate(
        rightStatue,
        [
          {
            opacity: 0,
            transform: 'translate3d(112px, 74px, 0) scale(0.92)',
            filter: 'blur(5px)',
          },
          {
            opacity: 1,
            transform: 'translate3d(0, 0, 0) scale(1)',
            filter: 'blur(0)',
          },
        ],
        {
          duration: 800,
          delay: 2200,
          easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
          fill: 'both',
        }
      )
    );

    heroAnimations.push(
      animate(
        centerStatue,
        [
          {
            opacity: 0,
            transform: 'translate3d(0, 112px, 0) scale(0.86)',
            filter: 'blur(7px)',
          },
          {
            opacity: 1,
            transform: 'translate3d(0, 0, 0) scale(1)',
            filter: 'blur(0)',
          },
        ],
        {
          duration: 800,
          delay: 1400,
          easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
          fill: 'both',
        }
      )
    );

    heroAnimations.push(
      animate(
        statueGroup,
        [
          { transform: 'scale(1)' },
          { transform: 'scale(1.42)' },
        ],
        {
          duration: 800,
          delay: 3670,
          easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
          fill: 'both',
        }
      )
    );

    window.setTimeout(function () {
      settle(hero, heroAnimations);
    }, HERO_DURATION);
  }

  observeOnce(hero, 0.08, playHero);

  /* Team reference: five continuous 2 s transitions, ending on Specialists. */
  var team = page.querySelector('[data-about-team]');
  var roles = team ? team.querySelectorAll('[data-team-role]') : [];
  var activeRole = 0;
  var TEAM_CADENCE = 2000;

  function renderTeam(index) {
    if (!roles.length) return;

    var previous = (index - 1 + roles.length) % roles.length;
    var next = index === 5 ? -1 : (index + 1) % roles.length;

    for (var i = 0; i < roles.length; i++) {
      var state = 'hidden';
      if (i === index) state = 'active';
      else if (i === previous) state = 'previous';
      else if (i === next) state = 'next';

      roles[i].setAttribute('data-state', state);
      roles[i].setAttribute('aria-hidden', i === index ? 'false' : 'true');
    }
  }

  function playTeam() {
    if (!team || team.classList.contains('is-running') || !roles.length) return;

    team.classList.add('is-running');
    renderTeam(activeRole);

    window.requestAnimationFrame(function () {
      activeRole = 1;
      renderTeam(activeRole);
    });

    for (var i = 2; i <= 5 && i < roles.length; i++) {
      (function (roleIndex) {
        teamTimers.push(
          window.setTimeout(function () {
            activeRole = roleIndex;
            renderTeam(activeRole);
          }, (roleIndex - 1) * TEAM_CADENCE)
        );
      })(i);
    }
  }

  observeOnce(team, 0.22, playTeam);

  /* Process reference: fixed 9.5 s timeline. */
  var process = page.querySelector('[data-about-process]');
  var PROCESS_DURATION = 9500;
  var PROCESS_COLLAPSE_START = 5330;
  var PROCESS_COLLAPSE_END = 6200;
  var PROCESS_CAPTION_START = 6200;
  var PROCESS_CAPTION_END = 8200;
  var PROCESS_BOWL_START = 8200;
  var PROCESS_BOWL_END = 9300;
  var STEP_DELAYS = [1430, 2030, 2570, 3030, 3570, 4170];

  function playProcess() {
    if (!process || process.classList.contains('is-running')) return;

    process.classList.add('is-running');
    var processAnimations = [];
    var heading = process.querySelector('[data-process-heading]');
    var path = process.querySelector('.mi-about-process__path');
    var ghost = process.querySelector('[data-process-ghost]');
    var steps = process.querySelectorAll('[data-process-step]');
    var caption = process.querySelector('[data-process-caption]');
    var bowl = process.querySelector('[data-process-bowl]');
    var bowlRect = bowl ? bowl.getBoundingClientRect() : process.getBoundingClientRect();
    var targetX = bowlRect.left + bowlRect.width * 0.5;
    var targetY = bowlRect.top + bowlRect.height * 0.11;

    processAnimations.push(
      animate(
        heading,
        [
          { opacity: 0, transform: 'translate3d(0, 44px, 0)', filter: 'blur(8px)' },
          { opacity: 1, transform: 'translate3d(0, 0, 0)', filter: 'blur(0)' },
        ],
        {
          duration: 900,
          delay: 100,
          easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
          fill: 'both',
        }
      )
    );

    processAnimations.push(
      animate(
        path,
        [
          { opacity: 0, transform: 'scale(0.82)', filter: 'blur(14px)' },
          { opacity: 0.86, transform: 'scale(1)', filter: 'blur(0)' },
        ],
        {
          duration: 470,
          delay: 830,
          easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
          fill: 'both',
        }
      )
    );

    processAnimations.push(
      animate(
        ghost,
        [
          { opacity: 0, transform: 'translate3d(0, 34px, 0) scale(0.84)', filter: 'blur(12px)' },
          { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)', filter: 'blur(0)' },
        ],
        {
          duration: 470,
          delay: 830,
          easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
          fill: 'both',
        }
      )
    );

    processAnimations.push(
      animate(
        ghost,
        [
          { opacity: 1, transform: 'scale(1)', filter: 'blur(0)' },
          { opacity: 0, transform: 'scale(0.24)', filter: 'blur(14px)' },
        ],
        {
          duration: PROCESS_COLLAPSE_END - PROCESS_COLLAPSE_START,
          delay: PROCESS_COLLAPSE_START,
          easing: 'cubic-bezier(0.55, 0, 0.45, 1)',
          fill: 'forwards',
        }
      )
    );

    for (var i = 0; i < steps.length; i++) {
      processAnimations.push(
        animate(
          steps[i],
          [
            { opacity: 0, transform: 'translate3d(0, 28px, 0) scale(0.84)', filter: 'blur(8px)' },
            { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)', filter: 'blur(0)' },
          ],
          {
            duration: 520,
            delay: STEP_DELAYS[i],
            easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
            fill: 'both',
          }
        )
      );

      var stepRect = steps[i].getBoundingClientRect();
      var deltaX = targetX - (stepRect.left + stepRect.width * 0.5);
      var deltaY = targetY - (stepRect.top + stepRect.height * 0.5);

      processAnimations.push(
        animate(
          steps[i],
          [
            { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)', filter: 'blur(0)' },
            {
              opacity: 0,
              transform: 'translate3d(' + deltaX + 'px, ' + deltaY + 'px, 0) scale(0.12)',
              filter: 'blur(12px)',
            },
          ],
          {
            duration: PROCESS_COLLAPSE_END - PROCESS_COLLAPSE_START,
            delay: PROCESS_COLLAPSE_START,
            easing: 'cubic-bezier(0.55, 0, 0.45, 1)',
            fill: 'forwards',
          }
        )
      );
    }

    var captionWords = caption ? caption.textContent.trim().split(/\s+/) : [];
    if (caption && captionWords.length) {
      caption.textContent = '';
      for (var wordIndex = 0; wordIndex < captionWords.length; wordIndex++) {
        var word = document.createElement('span');
        word.textContent = captionWords[wordIndex];
        caption.appendChild(word);
        if (wordIndex < captionWords.length - 1) caption.appendChild(document.createTextNode(' '));
      }

      processAnimations.push(
        animate(
          caption,
          [{ opacity: 0 }, { opacity: 1 }],
          {
            duration: 1,
            delay: PROCESS_CAPTION_START,
            easing: 'linear',
            fill: 'both',
          }
        )
      );

      var captionSpans = caption.querySelectorAll('span');
      var revealStagger = captionSpans.length > 1 ? 600 / (captionSpans.length - 1) : 0;
      var exitStagger = captionSpans.length > 1 ? 330 / (captionSpans.length - 1) : 0;

      for (var captionIndex = 0; captionIndex < captionSpans.length; captionIndex++) {
        processAnimations.push(
          animate(
            captionSpans[captionIndex],
            [
              { opacity: 0, transform: 'translate3d(0, 24px, 0)', filter: 'blur(7px)' },
              { opacity: 1, transform: 'translate3d(0, 0, 0)', filter: 'blur(0)' },
            ],
            {
              duration: 300,
              delay: PROCESS_CAPTION_START + captionIndex * revealStagger,
              easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
              fill: 'both',
            }
          )
        );

        processAnimations.push(
          animate(
            captionSpans[captionIndex],
            [
              { opacity: 1, transform: 'translate3d(0, 0, 0)', filter: 'blur(0)' },
              { opacity: 0, transform: 'translate3d(0, -18px, 0)', filter: 'blur(5px)' },
            ],
            {
              duration: 100,
              delay: 7770 + captionIndex * exitStagger,
              easing: 'linear',
              fill: 'forwards',
            }
          )
        );
      }
    }

    var aboutScale = parseFloat(window.getComputedStyle(page).getPropertyValue('--mi-about-s')) || 1;
    var bowlLift = -137 * aboutScale;

    processAnimations.push(
      animate(
        bowl,
        [
          { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)', filter: 'blur(0)', offset: 0 },
          { opacity: 1, transform: 'translate3d(0, ' + bowlLift * 0.72 + 'px, 0) scale(1.36)', filter: 'blur(0)', offset: 0.38 },
          { opacity: 1, transform: 'translate3d(0, ' + bowlLift + 'px, 0) scale(1.5)', filter: 'blur(0)', offset: 1 },
        ],
        {
          duration: PROCESS_BOWL_END - PROCESS_BOWL_START,
          delay: PROCESS_BOWL_START,
          easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
          fill: 'both',
        }
      )
    );

    window.setTimeout(function () {
      settle(process, processAnimations);
    }, PROCESS_DURATION);
  }

  observeOnce(process, 0.18, playProcess);

  window.addEventListener(
    'pagehide',
    function () {
      for (var timerIndex = 0; timerIndex < teamTimers.length; timerIndex++) {
        window.clearTimeout(teamTimers[timerIndex]);
      }
      for (var i = 0; i < runningAnimations.length; i++) {
        if (runningAnimations[i]) runningAnimations[i].cancel();
      }
    },
    { once: true }
  );
})();
