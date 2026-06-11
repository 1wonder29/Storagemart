(function () {
  document.querySelectorAll('.auth-toggle-pw[data-target]').forEach(function (toggleBtn) {
    const inputId = toggleBtn.getAttribute('data-target');
    const passwordInput = document.getElementById(inputId);
    if (!passwordInput) return;

    const eyeOpen = toggleBtn.querySelector('.icon-eye');
    const eyeClosed = toggleBtn.querySelector('.icon-eye-off');

    toggleBtn.addEventListener('click', function () {
      const isHidden = passwordInput.type === 'password';
      passwordInput.type = isHidden ? 'text' : 'password';
      toggleBtn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
      if (eyeOpen) eyeOpen.hidden = !isHidden;
      if (eyeClosed) eyeClosed.hidden = isHidden;
    });
  });

  const audioToggle = document.getElementById('authAudioToggle');
  const audioElement = document.getElementById('authAmbientAudio');
  if (!audioToggle || !audioElement) return;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion) return;

  const iconMuted = audioToggle.querySelector('.icon-muted');
  const iconPlaying = audioToggle.querySelector('.icon-playing');
  const hasTrackFile = Boolean(audioElement.getAttribute('src') || audioElement.querySelector('source'));
  let fileFailed = false;
  let ambientEngine = null;

  function setToggleState(isPlaying) {
    audioToggle.setAttribute('aria-pressed', isPlaying ? 'true' : 'false');
    audioToggle.setAttribute('aria-label', isPlaying ? 'Pause background music' : 'Play background music');
    audioToggle.title = isPlaying ? 'Pause music' : 'Play music';
    if (iconMuted) iconMuted.hidden = isPlaying;
    if (iconPlaying) iconPlaying.hidden = !isPlaying;
  }

  function stopAmbient() {
    if (!ambientEngine) return;
    ambientEngine.stop();
    ambientEngine = null;
  }

  function createAmbientEngine() {
    let context = null;
    const nodes = [];

    function start() {
      if (context) return;

      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;

      context = new AudioCtx();
      if (context.state === 'suspended') {
        context.resume();
      }

      const master = context.createGain();
      master.gain.value = 0.1;
      master.connect(context.destination);

      const padNotes = [110, 164.81, 220, 329.63];
      padNotes.forEach(function (frequency, index) {
        const oscillator = context.createOscillator();
        const gain = context.createGain();
        const lfo = context.createOscillator();
        const lfoGain = context.createGain();

        oscillator.type = 'sine';
        oscillator.frequency.value = frequency;
        gain.gain.value = 0.045 / padNotes.length;

        lfo.type = 'sine';
        lfo.frequency.value = 0.04 + index * 0.015;
        lfoGain.gain.value = 0.012;
        lfo.connect(lfoGain);
        lfoGain.connect(gain.gain);

        oscillator.connect(gain);
        gain.connect(master);
        oscillator.start();
        lfo.start();

        nodes.push(oscillator, gain, lfo, lfoGain);
      });

      nodes.push(master);
    }

    function stop() {
      nodes.forEach(function (node) {
        try {
          if (typeof node.stop === 'function') node.stop();
          node.disconnect();
        } catch (error) {
          /* ignore teardown errors */
        }
      });
      nodes.length = 0;

      if (context) {
        context.close();
        context = null;
      }
    }

    return { start: start, stop: stop };
  }

  function startAmbient() {
    if (!ambientEngine) ambientEngine = createAmbientEngine();
    if (ambientEngine) ambientEngine.start();
  }

  function pauseAudio() {
    audioElement.pause();
    stopAmbient();
    setToggleState(false);
  }

  function playAudio() {
    stopAmbient();

    if (hasTrackFile && !fileFailed) {
      const playPromise = audioElement.play();
      if (playPromise && typeof playPromise.then === 'function') {
        playPromise
          .then(function () {
            setToggleState(true);
          })
          .catch(function () {
            startAmbient();
            setToggleState(true);
          });
        return;
      }
    }

    startAmbient();
    setToggleState(true);
  }

  audioToggle.addEventListener('click', function () {
    const isPlaying = audioToggle.getAttribute('aria-pressed') === 'true';
    if (isPlaying) pauseAudio();
    else playAudio();
  });

  audioElement.addEventListener('error', function () {
    fileFailed = true;
    if (audioToggle.getAttribute('aria-pressed') === 'true' && audioElement.paused) {
      startAmbient();
    }
  });

  audioElement.volume = 0.5;
  audioElement.load();

  audioToggle.hidden = false;
  setToggleState(false);

  document.addEventListener('visibilitychange', function () {
    if (document.hidden && audioToggle.getAttribute('aria-pressed') === 'true') {
      pauseAudio();
    }
  });
})();
