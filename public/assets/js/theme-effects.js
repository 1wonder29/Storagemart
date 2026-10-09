/*
 * Screen effects for the seasonal themes (the list is in app/Helpers/SeasonalThemes.php, EFFECTS).
 * TMSEffects.play(['rain', 'zombies'], theme, seconds) plays the effects together over the page;
 * theme = { items: [emoji...], colors: [main, accent] }. Plain DOM + CSS animations (theme-effects.css),
 * nothing to click, removed when finished. Lighter (fewer pieces) when the computer asks for less motion.
 * Anti-spam: effects never pile up. A new play() first clears the ones still running, and a play()
 * within 3 seconds of the last one is ignored, so pressing the button again and again cannot slow the PC.
 */
(function () {
  'use strict';
  if (window.TMSEffects) return;

  var lessMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function rand(min, max) { return min + Math.random() * (max - min); }
  function pick(list) { return list[Math.floor(Math.random() * list.length)]; }
  function count(n) { return Math.max(1, Math.round(lessMotion ? n / 2 : n)); }
  function span(cls, text) {
    var node = document.createElement('span');
    node.className = cls;
    if (text) node.textContent = text;
    return node;
  }
  var running = [];   // layers on the page now
  var lastPlay = 0;
  var busyUntil = 0;
  var MIN_GAP = 3000;  // ms between two plays

  function layer(seconds, extra) {
    var node = document.createElement('div');
    node.className = 'tfx-layer';
    node.setAttribute('aria-hidden', 'true');
    document.body.appendChild(node);
    running.push(node);
    setTimeout(function () { node.classList.add('tfx-out'); }, seconds * 1000);
    setTimeout(function () { drop(node); }, (seconds + (extra || 2)) * 1000);
    return node;
  }
  function drop(node) {
    node.remove();
    var i = running.indexOf(node);
    if (i !== -1) running.splice(i, 1);
  }
  // Pieces that keep repeating stop being created after `seconds`; the layer then fades out.
  function anim(node, name, duration, delay, extra) {
    node.style.animation = name + ' ' + duration + 's ' + (extra || 'linear') + ' ' + delay + 's both';
    return node;
  }

  // Something falling from the top (wrapper falls, inner sways / spins).
  function faller(root, text, size, duration, delay, cls) {
    var outer = span('tfx-fall-outer');
    outer.style.left = rand(0, 100) + 'vw';
    anim(outer, 'tfx-fall', duration, delay);
    var inner = span('tfx-sway ' + (cls || ''), text);
    inner.style.fontSize = size + 'px';
    inner.style.animationDuration = rand(1.6, 3.2) + 's';
    outer.appendChild(inner);
    root.appendChild(outer);
    return inner;
  }
  // Something rising from the bottom.
  function riser(root, text, size, duration, delay, cls) {
    var outer = span('tfx-rise-outer');
    outer.style.left = rand(0, 96) + 'vw';
    anim(outer, 'tfx-rise', duration, delay, 'ease-in');
    var inner = span('tfx-sway ' + (cls || ''), text);
    inner.style.fontSize = size + 'px';
    inner.style.animationDuration = rand(1.8, 3.4) + 's';
    outer.appendChild(inner);
    root.appendChild(outer);
    return inner;
  }

  var EFFECTS = {
    rain: function (s) {
      var root = layer(s, 1.5);
      root.appendChild(span('tfx-dim'));
      var flash = span('tfx-flash');
      flash.style.animationDuration = s + 's';
      root.appendChild(flash);
      for (var i = 0; i < count(110); i++) {
        var drop = span('tfx-drop');
        drop.style.left = rand(-10, 105) + 'vw';
        drop.style.height = rand(14, 26) + 'px';
        drop.style.animation = 'tfx-rain ' + rand(0.45, 0.85) + 's linear ' + rand(0, 1.2) + 's infinite';
        root.appendChild(drop);
      }
    },
    zombies: function (s) {
      var root = layer(Math.max(s, 16), 2);
      var faces = ['🧟', '🧟‍♂️', '🧟‍♀️'];
      for (var i = 0; i < count(5); i++) {
        var walker = span('tfx-walker');
        walker.style.bottom = rand(0, 18) + 'px';
        anim(walker, 'tfx-walk-left', rand(13, 19), rand(0, 5));
        var body = span('tfx-bob', pick(faces));
        body.style.fontSize = rand(52, 76) + 'px';
        body.style.animationDuration = rand(0.7, 1.1) + 's';
        walker.appendChild(body);
        root.appendChild(walker);
      }
    },
    bats: function (s) {
      var root = layer(s, 2);
      for (var i = 0; i < count(9); i++) {
        var fly = span('tfx-flyer');
        fly.style.top = rand(10, 60) + 'vh';
        var toLeft = Math.random() < 0.5;
        anim(fly, toLeft ? 'tfx-cross-left' : 'tfx-cross-right', rand(5, 9), rand(0, s - 4));
        var bat = span('tfx-flutter', '🦇');
        bat.style.fontSize = rand(24, 44) + 'px';
        if (!toLeft) bat.style.transform = 'scaleX(-1)';
        fly.appendChild(bat);
        root.appendChild(fly);
      }
    },
    ghosts: function (s) {
      var root = layer(s, 3);
      for (var i = 0; i < count(8); i++) {
        riser(root, '👻', rand(30, 54), rand(7, 11), rand(0, s - 5), 'tfx-ghost');
      }
    },
    snow: function (s) {
      var root = layer(s, 4);
      for (var i = 0; i < count(70); i++) {
        faller(root, pick(['❄', '❅', '❆', '•']), rand(12, 28), rand(7, 13), rand(0, s - 3), 'tfx-snow');
      }
    },
    sleigh: function (s) {
      var root = layer(Math.max(s, 10), 1);
      var ride = span('tfx-flyer');
      ride.style.top = rand(10, 18) + 'vh';
      anim(ride, 'tfx-cross-left', 9, 0.5);
      var sleigh = span('tfx-bob tfx-sleigh', '🦌🦌🛷🎅');
      sleigh.style.fontSize = '46px';
      sleigh.style.animationDuration = '1.2s';
      ride.appendChild(sleigh);
      root.appendChild(ride);
      for (var i = 0; i < count(18); i++) {
        var star = span('tfx-trail', '✦');
        star.style.top = 'calc(' + ride.style.top + ' + ' + rand(20, 60) + 'px)';
        anim(star, 'tfx-twinkle', 1.4, 0.5 + i * 0.4);
        star.style.left = (100 - i * 5.5) + 'vw';
        root.appendChild(star);
      }
    },
    lights: function (s) {
      var root = layer(s, 1);
      var strand = span('tfx-strand');
      var colors = ['#e74a3b', '#1cc88a', '#f6c23e', '#4e73df', '#ff7ab6'];
      var n = Math.max(12, Math.round(window.innerWidth / 46));
      for (var i = 0; i < n; i++) {
        var bulb = span('tfx-bulb');
        bulb.style.left = (i / (n - 1) * 100) + '%';
        bulb.style.top = (6 + Math.sin(i / (n - 1) * Math.PI * 6) * 6 + 6) + 'px';
        bulb.style.background = colors[i % colors.length];
        bulb.style.color = colors[i % colors.length];
        bulb.style.animationDelay = (i % 3) * 0.35 + 's';
        strand.appendChild(bulb);
      }
      root.appendChild(strand);
    },
    fireworks: function (s, theme) {
      var root = layer(s, 2);
      var palette = (theme.colors || []).concat(['#f6c23e', '#ff6b6b', '#4ecdc4', '#ffffff', '#a78bfa']);
      for (var b = 0; b < count(8); b++) {
        (function (delay) {
          var x = rand(12, 88), y = rand(14, 50);
          var rocket = span('tfx-rocket');
          rocket.style.left = x + 'vw';
          rocket.style.setProperty('--tfx-y', y + 'vh');
          anim(rocket, 'tfx-launch', 0.7, delay, 'ease-out');
          root.appendChild(rocket);
          var color = pick(palette);
          for (var i = 0; i < 26; i++) {
            var spark = span('tfx-spark');
            var angle = (i / 26) * Math.PI * 2, dist = rand(70, 140);
            spark.style.left = x + 'vw';
            spark.style.top = y + 'vh';
            spark.style.background = spark.style.color = Math.random() < 0.3 ? pick(palette) : color;
            spark.style.setProperty('--tfx-dx', Math.cos(angle) * dist + 'px');
            spark.style.setProperty('--tfx-dy', Math.sin(angle) * dist + 'px');
            anim(spark, 'tfx-burst', 1.3, delay + 0.7, 'ease-out');
            root.appendChild(spark);
          }
        })(rand(0, Math.max(1, s - 2)));
      }
    },
    confetti: function (s, theme) {
      var root = layer(s, 5);
      var palette = (theme.colors || []).concat(['#f6c23e', '#1cc88a', '#e74a3b', '#36b9cc', '#ffffff']);
      for (var i = 0; i < count(100); i++) {
        var piece = faller(root, '', 10, rand(4, 8), rand(0, s - 4), 'tfx-confetti');
        piece.style.background = pick(palette);
        piece.style.width = rand(6, 11) + 'px';
        piece.style.height = rand(8, 16) + 'px';
      }
    },
    sparkles: function (s) {
      var root = layer(s, 1);
      for (var i = 0; i < count(34); i++) {
        var star = span('tfx-sparkle', pick(['✨', '✦', '✧']));
        star.style.left = rand(2, 96) + 'vw';
        star.style.top = rand(8, 90) + 'vh';
        star.style.fontSize = rand(14, 30) + 'px';
        star.style.animation = 'tfx-twinkle ' + rand(1.2, 2.2) + 's ease-in-out ' + rand(0, 2) + 's infinite';
        root.appendChild(star);
      }
    },
    hearts: function (s) {
      var root = layer(s, 4);
      for (var i = 0; i < count(30); i++) {
        riser(root, pick(['❤️', '💕', '💖', '💗', '💘']), rand(18, 40), rand(6, 10), rand(0, s - 4));
      }
    },
    petals: function (s) {
      var root = layer(s, 4);
      for (var i = 0; i < count(34); i++) {
        faller(root, pick(['🌸', '🌸', '🌺', '🌹']), rand(16, 30), rand(6, 11), rand(0, s - 3), 'tfx-spin');
      }
    },
    sun: function (s) {
      var root = layer(s, 1.5);
      root.appendChild(span('tfx-warm'));
      var sun = span('tfx-sun');
      sun.appendChild(span('tfx-sun-rays'));
      sun.appendChild(span('tfx-sun-core'));
      root.appendChild(sun);
    },
    bubbles: function (s) {
      var root = layer(s, 4);
      for (var i = 0; i < count(32); i++) {
        var bubble = riser(root, '', 10, rand(6, 11), rand(0, s - 4), 'tfx-bubble');
        var size = rand(12, 42);
        bubble.style.width = size + 'px';
        bubble.style.height = size + 'px';
      }
    },
    leaves: function (s) {
      var root = layer(s, 4);
      for (var i = 0; i < count(30); i++) {
        faller(root, pick(['🍃', '🌿', '🌴', '🌺', '🌼']), rand(18, 34), rand(6, 11), rand(0, s - 3), 'tfx-spin');
      }
    },
    waves: function (s, theme) {
      var root = layer(s, 1.5);
      var color = (theme.colors && theme.colors[0]) || '#00b894';
      var svg = '<svg viewBox="0 0 1200 100" preserveAspectRatio="none"><path d="M0 50 C 150 0 300 100 450 50 S 750 0 900 50 S 1200 100 1200 50 L 1200 100 L 0 100 Z" fill="' + color + '"/></svg>';
      ['tfx-wave tfx-wave-back', 'tfx-wave'].forEach(function (cls) {
        var wave = span(cls);
        wave.innerHTML = svg + svg;
        root.appendChild(wave);
      });
    },
    flags: function (s) {
      var root = layer(s, 4);
      for (var i = 0; i < count(26); i++) {
        var flag = faller(root, '', 10, rand(6, 11), rand(0, s - 3), 'tfx-flag');
        var w = rand(26, 40);
        flag.style.width = w + 'px';
        flag.style.height = (w / 2) + 'px';
      }
    },
    stars: function (s) {
      var root = layer(s, 4);
      for (var i = 0; i < count(26); i++) {
        faller(root, pick(['⭐', '🌟', '✦']), rand(14, 30), rand(6, 11), rand(0, s - 3), 'tfx-spin tfx-gold');
      }
    },
    balloons: function (s) {
      var root = layer(s, 4);
      var tints = [0, 40, 160, 200, 280, 320];
      for (var i = 0; i < count(22); i++) {
        var balloon = riser(root, '🎈', rand(30, 52), rand(7, 11), rand(0, s - 4));
        balloon.style.filter = 'hue-rotate(' + pick(tints) + 'deg)';
      }
    },
    items: function (s, theme) {
      var root = layer(s, 4);
      var items = theme.items && theme.items.length ? theme.items : ['✨'];
      for (var i = 0; i < count(36); i++) {
        faller(root, items[i % items.length], rand(16, 36), rand(5, 10), rand(0, s - 3), 'tfx-spin');
      }
    },
  };

  window.TMSEffects = {
    names: Object.keys(EFFECTS),
    /** Plays the effects; returns false when pressed again too soon (nothing happens). */
    play: function (keys, theme, seconds) {
      var now = Date.now();
      if (now - lastPlay < MIN_GAP) return false;
      lastPlay = now;
      this.stop();
      seconds = seconds || 10;
      theme = theme || {};
      (keys && keys.length ? keys : ['items']).forEach(function (key) {
        if (EFFECTS[key]) {
          try { EFFECTS[key](seconds, theme); } catch (e) { /* one effect failing never breaks the page */ }
        }
      });
      busyUntil = now + (seconds + 2) * 1000;
      return true;
    },
    /** Removes every effect still on the screen. */
    stop: function () {
      running.slice().forEach(drop);
      busyUntil = 0;
    },
    /** Seconds until the effects now playing are finished (0 = none). */
    remaining: function () {
      return Math.max(0, Math.ceil((busyUntil - Date.now()) / 1000));
    },
  };
})();
