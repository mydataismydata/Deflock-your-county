/* ===========================================================================
   Deflock — motion and the menu
   ---------------------------------------------------------------------------
   Four behaviours, no dependencies and no build step:

     1. headings arrive one word at a time when they come into view
     2. a scene's photograph brightens as the scene crosses the screen
     3. the header turns solid once the page has moved
     4. the menu collapses behind a button on a narrow screen

   Nothing in 1 to 3 runs when the reader has asked for reduced motion. The
   menu always works, including with scripting off: the markup ships open and
   this file is what closes it.
   =========================================================================== */

(function () {
  'use strict';

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* --- 1. word reveal ---------------------------------------------------- */

  function splitWords(target, text, bucket) {
    text.split(/(\s+)/).forEach(function (chunk) {
      if (chunk === '') { return; }
      if (/^\s+$/.test(chunk)) {
        target.appendChild(document.createTextNode(chunk));
        return;
      }
      var span = document.createElement('span');
      span.className = 'word';
      span.textContent = chunk;
      target.appendChild(span);
      bucket.push(span);
    });
  }

  function prepare(el) {
    var source = Array.prototype.slice.call(el.childNodes);
    var words = [];
    el.textContent = '';
    source.forEach(function (node) {
      if (node.nodeType === 3) {
        splitWords(el, node.textContent || '', words);
      } else if (node.nodeType === 1) {
        /* The wrapper is kept, which is how an accent phrase keeps its colour,
           and the text inside it is split. */
        var shell = node.cloneNode(false);
        el.appendChild(shell);
        splitWords(shell, node.textContent || '', words);
      } else {
        el.appendChild(node.cloneNode(true));
      }
    });
    el.setAttribute('data-reveal-ready', '1');
    return words;
  }

  function revealWords(el, words) {
    var running = words.map(function (word, i) {
      return word.animate(
        [
          { opacity: 0, transform: 'translateY(0.55em)' },
          { opacity: 1, transform: 'translateY(0)' }
        ],
        {
          duration: 750,
          delay: i * 50,
          easing: 'cubic-bezier(0.165, 0.84, 0.44, 1)',
          fill: 'both'
        }
      );
    });

    /* The words are held at zero opacity by the animation's own fill, so a tab
       that stops painting would keep a heading invisible for as long as it
       stayed in the background. Marking the heading done hands the words back
       to the stylesheet, which shows them. The timer is the floor: it fires
       whether or not a single frame was ever drawn. */
    var done = false;
    function finish() {
      if (done) { return; }
      done = true;
      el.setAttribute('data-reveal-done', '1');
      running.forEach(function (a) { a.cancel(); });
    }

    var last = running[running.length - 1];
    if (last && last.finished && last.finished.then) {
      last.finished.then(finish, function () {});
    }
    window.setTimeout(finish, words.length * 50 + 750 + 250);
  }

  function startReveals() {
    var heads = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));
    if (!heads.length || reduced || !window.IntersectionObserver) { return; }
    if (typeof Element.prototype.animate !== 'function') { return; }

    var pending = new Map();
    heads.forEach(function (el) { pending.set(el, prepare(el)); });

    function fire(el) {
      var words = pending.get(el);
      if (!words) { return; }
      pending.delete(el);
      observer.unobserve(el);
      if (words.length) { revealWords(el, words); }
    }

    /* The words are hidden the moment they are split, so a heading that never
       gets its callback would never come back. A throttled tab is the usual
       way that happens. Anything already on screen is revealed outright rather
       than waited for, and whatever is left is checked again when the tab
       comes back to the front. */
    function revealWhatIsVisible() {
      Array.from(pending.keys()).forEach(function (el) {
        var rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight * 0.85 && rect.bottom > 0) { fire(el); }
      });
    }

    /* Fires when the heading is 15% up from the bottom edge, and once only. A
       heading that re-animates on every pass is a heading nobody can read. */
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) { fire(entry.target); }
      });
    }, { rootMargin: '0px 0px -15% 0px', threshold: 0 });

    heads.forEach(function (el) { observer.observe(el); });
    revealWhatIsVisible();

    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) { revealWhatIsVisible(); }
    });
  }

  /* --- 2. scenes ---------------------------------------------------------- */

  /* Progress runs 0 to 1 between the scene's top reaching 80% of the screen and
     its bottom reaching 20%. */
  function sceneProgress(el) {
    var vh = window.innerHeight;
    var rect = el.getBoundingClientRect();
    var total = rect.height + vh * 0.6;
    return Math.min(1, Math.max(0, (vh * 0.8 - rect.top) / total));
  }

  /* The photograph is dimmest as the scene arrives and as it leaves, and holds
     at its set opacity across the middle. The floor stops a scene from ever
     going fully black behind its own words. */
  function ramp(p) {
    var edge = p < 0.2 ? p / 0.2 : p > 0.8 ? (1 - p) / 0.2 : 1;
    return Math.max(0.2, edge);
  }

  function startScenes() {
    var scenes = Array.prototype.slice.call(document.querySelectorAll('[data-scene]'));
    if (!scenes.length || reduced) { return; }

    var media = scenes.map(function (s) { return s.querySelector('.scene__media'); });
    var base = media.map(function (m) {
      return m ? (parseFloat(getComputedStyle(m).opacity) || 0.55) : 0;
    });

    var queued = false;
    function paint() {
      queued = false;
      scenes.forEach(function (scene, i) {
        if (!media[i]) { return; }
        media[i].style.opacity = String(base[i] * ramp(sceneProgress(scene)));
      });
    }
    function onScroll() {
      if (queued) { return; }
      queued = true;
      requestAnimationFrame(paint);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    paint();
  }

  /* --- 3. header ---------------------------------------------------------- */

  function startHeader() {
    var header = document.querySelector('.site-header');
    if (!header) { return; }
    var queued = false;
    function paint() {
      queued = false;
      header.setAttribute('data-scrolled', window.scrollY > 24 ? 'true' : 'false');
    }
    window.addEventListener('scroll', function () {
      if (queued) { return; }
      queued = true;
      requestAnimationFrame(paint);
    }, { passive: true });
    paint();
  }

  /* --- 4. menu ------------------------------------------------------------ */

  function startMenu() {
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('primary-nav');
    if (!toggle || !nav) { return; }

    var narrow = window.matchMedia('(max-width: 48rem)');

    function apply() {
      if (narrow.matches) {
        nav.hidden = toggle.getAttribute('aria-expanded') !== 'true';
      } else {
        nav.hidden = false;
      }
    }

    toggle.hidden = false;
    toggle.setAttribute('aria-expanded', 'false');

    toggle.addEventListener('click', function () {
      var open = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
      apply();
    });

    /* A link inside the open menu closes it on the way out. */
    nav.addEventListener('click', function (event) {
      if (event.target.closest('a') && narrow.matches) {
        toggle.setAttribute('aria-expanded', 'false');
        apply();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        toggle.setAttribute('aria-expanded', 'false');
        apply();
        toggle.focus();
      }
    });

    if (typeof narrow.addEventListener === 'function') {
      narrow.addEventListener('change', apply);
    } else if (typeof narrow.addListener === 'function') {
      narrow.addListener(apply);
    }

    apply();
  }

  function boot() {
    startReveals();
    startScenes();
    startHeader();
    startMenu();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
  } else {
    boot();
  }
})();
