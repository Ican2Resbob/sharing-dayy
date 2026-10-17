(() => {
  'use strict';

  /* ---------- Element references ---------- */
  const slides = Array.from(document.querySelectorAll('.slide'));
  const total = slides.length;

  const prevBtn = document.getElementById('prev-btn');
  const nextBtn = document.getElementById('next-btn');
  const fsBtn = document.getElementById('fullscreen-btn');
  const fsLabel = fsBtn.querySelector('.btn__label');
  const counter = document.getElementById('counter');
  const slideLabel = document.getElementById('slide-label');
  const progress = document.getElementById('progress');
  const progressBar = document.getElementById('progress-bar');
  const dotButtons = Array.from(document.querySelectorAll('[data-goto]'));
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  let current = -1;

  const pad = (n) => String(n).padStart(2, '0');
  const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

  /* ---------- Terminal typing animation ---------- */
  // Each terminal body keeps a token. Starting a new run (or stopping)
  // replaces the token, which cancels any older run still in progress.
  const runTokens = new WeakMap();

  async function typeTerminal(body) {
    const token = Symbol('run');
    runTokens.set(body, token);
    body.classList.remove('is-done');

    const items = Array.from(body.querySelectorAll('.t-line')).map((line) => {
      const textEl = line.querySelector('.t-text');
      return { line, textEl, text: textEl.dataset.text || '' };
    });

    if (reduceMotion.matches) {
      items.forEach(({ line, textEl, text }) => {
        line.hidden = false;
        line.classList.remove('is-typing');
        textEl.textContent = text;
      });
      body.classList.add('is-done');
      return;
    }

    items.forEach(({ line, textEl }) => {
      line.hidden = true;
      line.classList.remove('is-typing');
      textEl.textContent = '';
    });

    await sleep(350);

    for (const { line, textEl, text } of items) {
      if (runTokens.get(body) !== token) return;

      const isCommand = line.classList.contains('t-line--cmd');
      line.hidden = false;
      line.classList.add('is-typing');

      for (let i = 1; i <= text.length; i++) {
        if (runTokens.get(body) !== token) return;
        textEl.textContent = text.slice(0, i);
        await sleep(isCommand ? 55 : 22);
      }

      line.classList.remove('is-typing');
      await sleep(isCommand ? 350 : 180);
    }

    if (runTokens.get(body) === token) body.classList.add('is-done');
  }

  function stopTerminal(body) {
    runTokens.delete(body);
  }

  document.querySelectorAll('[data-replay]').forEach((button) => {
    button.addEventListener('click', () => {
      const body = button.closest('.terminal').querySelector('[data-typer]');
      typeTerminal(body);
    });
  });

  /* ---------- Slide navigation ---------- */
  function updateUI() {
    counter.textContent = `${pad(current + 1)} / ${pad(total)}`;
    slideLabel.textContent = slides[current].dataset.title || '';

    progressBar.style.width = `${((current + 1) / total) * 100}%`;
    progress.setAttribute('aria-valuenow', String(current + 1));

    prevBtn.disabled = current === 0;
    nextBtn.disabled = current === total - 1;

    dotButtons.forEach((dot, index) => {
      const active = index === current;
      dot.classList.toggle('is-active', active);
      if (active) dot.setAttribute('aria-current', 'step');
      else dot.removeAttribute('aria-current');
    });
  }

  function goTo(index) {
    const target = Math.min(Math.max(index, 0), total - 1);
    if (target === current) return;

    const previous = current;
    current = target;

    slides.forEach((slide, i) => {
      const active = i === current;
      slide.classList.toggle('is-active', active);
      slide.classList.toggle('is-before', i < current);
      slide.classList.toggle('is-after', i > current);
      slide.setAttribute('aria-hidden', String(!active));
      slide.toggleAttribute('inert', !active);
    });

    if (previous >= 0) {
      slides[previous].querySelectorAll('[data-typer]').forEach(stopTerminal);
    }
    slides[current].scrollTop = 0;
    slides[current].querySelectorAll('[data-typer]').forEach(typeTerminal);

    updateUI();

    try {
      history.replaceState(null, '', `#${current + 1}`);
    } catch (error) {
      /* Some browsers block this for local files. It is safe to ignore. */
    }
  }

  const next = () => goTo(current + 1);
  const prev = () => goTo(current - 1);

  prevBtn.addEventListener('click', prev);
  nextBtn.addEventListener('click', next);
  dotButtons.forEach((dot) => {
    dot.addEventListener('click', () => goTo(Number(dot.dataset.goto)));
  });

  /* ---------- Fullscreen ---------- */
  function updateFullscreenUI() {
    const isFullscreen = Boolean(document.fullscreenElement);
    fsLabel.textContent = isFullscreen ? 'Exit fullscreen' : 'Fullscreen';
    fsBtn.setAttribute('aria-pressed', String(isFullscreen));
  }

  async function toggleFullscreen() {
    try {
      if (document.fullscreenElement) {
        await document.exitFullscreen();
      } else {
        await document.documentElement.requestFullscreen();
      }
    } catch (error) {
      console.warn('Fullscreen request failed:', error);
    }
  }

  if (!document.fullscreenEnabled) {
    fsBtn.disabled = true;
    fsBtn.title = 'Fullscreen is not supported in this browser';
  }

  fsBtn.addEventListener('click', toggleFullscreen);
  // The Escape key exits fullscreen natively; this event keeps the button label in sync.
  document.addEventListener('fullscreenchange', updateFullscreenUI);

  /* ---------- Keyboard controls ---------- */
  // Only arrow keys, Home, End, and "F" are handled. Enter and Space are left
  // alone, so they keep working normally on focused buttons.
  document.addEventListener('keydown', (event) => {
    if (event.defaultPrevented || event.altKey || event.ctrlKey || event.metaKey) return;

    const target = event.target;
    if (target instanceof HTMLElement) {
      const isTextField = target.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(target.tagName);
      if (isTextField) return;
    }

    switch (event.key) {
      case 'ArrowRight': event.preventDefault(); next(); break;
      case 'ArrowLeft':  event.preventDefault(); prev(); break;
      case 'Home':       event.preventDefault(); goTo(0); break;
      case 'End':        event.preventDefault(); goTo(total - 1); break;
      case 'f':
      case 'F':          event.preventDefault(); toggleFullscreen(); break;
      default: break;
    }
  });

  /* ---------- Start (supports #3 in the URL) ---------- */
  window.addEventListener('hashchange', () => {
    const number = parseInt(window.location.hash.slice(1), 10);
    if (!Number.isNaN(number)) goTo(number - 1);
  });

  const startNumber = parseInt(window.location.hash.slice(1), 10);
  goTo(Number.isNaN(startNumber) ? 0 : startNumber - 1);
  updateFullscreenUI();
})();