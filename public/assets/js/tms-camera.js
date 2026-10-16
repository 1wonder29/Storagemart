/*
 * "Take a photo" with the device camera (laptop webcam or phone camera).
 *
 *   TMSCamera.take({ fallbackInput: inputEl }).then(function (file) { if (file) use(file); });
 *
 * The browser asks the person to allow the camera the first time. If the camera cannot be
 * used (blocked, no camera, or an old browser), the window offers "Choose a photo instead",
 * which opens fallbackInput (a normal <input type="file" accept="image/*" capture>).
 */
(function () {
  'use strict';
  if (window.TMSCamera) return;

  var CSS = '' +
    '.tmscam{position:fixed;inset:0;z-index:20010;background:rgba(10,15,30,.86);display:flex;align-items:center;justify-content:center;padding:16px}' +
    '.tmscam-box{width:100%;max-width:560px;background:#111827;color:#f3f4f6;border-radius:18px;overflow:hidden;box-shadow:0 24px 70px rgba(0,0,0,.55)}' +
    '.tmscam-head{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:linear-gradient(135deg,#19398A,#2661D4)}' +
    '.tmscam-head h2{margin:0;font-size:1rem;font-weight:700;color:#fff}' +
    '.tmscam-x{background:rgba(255,255,255,.15);border:0;color:#fff;width:34px;height:34px;border-radius:50%;font-size:18px;line-height:1;cursor:pointer}' +
    '.tmscam-stage{position:relative;background:#000;aspect-ratio:4/3;display:flex;align-items:center;justify-content:center}' +
    '.tmscam-stage video,.tmscam-stage img{width:100%;height:100%;object-fit:contain;display:block}' +
    '.tmscam-msg{padding:28px 20px;text-align:center;font-size:.95rem;line-height:1.5;color:#e5e7eb}' +
    '.tmscam-msg i{display:block;font-size:30px;margin-bottom:10px;color:#F85F00}' +
    '.tmscam-bar{display:flex;align-items:center;justify-content:center;gap:12px;padding:14px;flex-wrap:wrap}' +
    '.tmscam-btn{border:0;border-radius:999px;padding:.55rem 1.1rem;font-weight:600;font-size:.9rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px}' +
    '.tmscam-btn:focus-visible{outline:3px solid rgba(248,95,0,.6);outline-offset:2px}' +
    '.tmscam-ghost{background:#374151;color:#f3f4f6}.tmscam-ghost:hover{background:#4b5563}' +
    '.tmscam-go{background:linear-gradient(135deg,#F85F00,#ff8a3d);color:#fff}' +
    '.tmscam-shoot{width:64px;height:64px;padding:0;border-radius:50%;background:#fff;border:5px solid #F85F00;justify-content:center;color:#F85F00;font-size:22px}' +
    '.tmscam-shoot:hover{transform:scale(1.04)}' +
    '.tmscam [hidden]{display:none!important}';

  function addStyles() {
    if (document.getElementById('tms-camera-css')) return;
    var style = document.createElement('style');
    style.id = 'tms-camera-css';
    style.textContent = CSS;
    (document.head || document.documentElement).appendChild(style);
  }

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  }

  function button(cls, iconCls, label) {
    var b = el('button', 'tmscam-btn ' + cls);
    b.type = 'button';
    if (iconCls) {
      var i = el('i', iconCls);
      i.setAttribute('aria-hidden', 'true');
      b.appendChild(i);
    }
    if (label) b.appendChild(document.createTextNode(label));
    return b;
  }

  function supported() {
    return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia && window.isSecureContext !== false);
  }

  function take(options) {
    options = options || {};
    var fallbackInput = options.fallbackInput || null;
    if (!supported()) {
      // Old browser or a page without https: the phone's own camera app opens instead.
      if (fallbackInput) fallbackInput.click();
      return Promise.resolve(null);
    }
    addStyles();

    return new Promise(function (resolve) {
      var stream = null;
      var facing = 'environment';
      var shotBlob = null;
      var finished = false;

      var overlay = el('div', 'tmscam');
      overlay.setAttribute('role', 'dialog');
      overlay.setAttribute('aria-modal', 'true');
      overlay.setAttribute('aria-label', 'Take a photo');
      var box = el('div', 'tmscam-box');
      var head = el('div', 'tmscam-head');
      head.appendChild(el('h2', null, 'Take a photo'));
      var x = el('button', 'tmscam-x', '×');
      x.type = 'button';
      x.setAttribute('aria-label', 'Close');
      head.appendChild(x);

      var stage = el('div', 'tmscam-stage');
      var video = el('video');
      video.setAttribute('playsinline', '');
      video.muted = true;
      video.autoplay = true;
      var still = el('img');
      still.alt = 'Photo you took';
      still.hidden = true;
      var msg = el('div', 'tmscam-msg');
      msg.hidden = true;
      stage.appendChild(video);
      stage.appendChild(still);
      stage.appendChild(msg);

      var bar = el('div', 'tmscam-bar');
      var flip = button('tmscam-ghost', 'fas fa-sync-alt', 'Switch camera');
      flip.hidden = true;
      var shoot = button('tmscam-shoot', 'fas fa-camera', '');
      shoot.setAttribute('aria-label', 'Take the photo');
      var retake = button('tmscam-ghost', 'fas fa-redo', 'Retake');
      retake.hidden = true;
      var use = button('tmscam-go', 'fas fa-check', 'Use photo');
      use.hidden = true;
      var choose = button('tmscam-ghost', 'fas fa-images', 'Choose a photo instead');
      choose.hidden = !fallbackInput;
      [flip, shoot, retake, use, choose].forEach(function (b) { bar.appendChild(b); });

      box.appendChild(head);
      box.appendChild(stage);
      box.appendChild(bar);
      overlay.appendChild(box);
      document.body.appendChild(overlay);

      function stop() {
        if (stream) {
          stream.getTracks().forEach(function (t) { t.stop(); });
          stream = null;
        }
      }
      function finish(file) {
        if (finished) return;
        finished = true;
        stop();
        document.removeEventListener('keydown', onKey, true);
        overlay.remove();
        resolve(file || null);
      }
      function onKey(e) {
        if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); finish(null); }
      }
      function showMessage(iconCls, text) {
        video.hidden = true;
        still.hidden = true;
        shoot.hidden = true;
        flip.hidden = true;
        msg.textContent = '';
        var i = el('i', iconCls);
        i.setAttribute('aria-hidden', 'true');
        msg.appendChild(i);
        msg.appendChild(document.createTextNode(text));
        msg.hidden = false;
        choose.hidden = !fallbackInput;
        if (fallbackInput) choose.focus();
      }
      function start() {
        stop();
        video.hidden = false;
        still.hidden = true;
        msg.hidden = true;
        shoot.hidden = false;
        retake.hidden = true;
        use.hidden = true;
        shoot.disabled = true;
        navigator.mediaDevices.getUserMedia({
          video: { facingMode: { ideal: facing }, width: { ideal: 1920 }, height: { ideal: 1440 } },
          audio: false
        }).then(function (s) {
          if (finished) { s.getTracks().forEach(function (t) { t.stop(); }); return; }
          stream = s;
          video.srcObject = s;
          var playing = video.play();
          if (playing && playing.catch) playing.catch(function () {});
          shoot.disabled = false;
          shoot.focus();
          if (navigator.mediaDevices.enumerateDevices) {
            navigator.mediaDevices.enumerateDevices().then(function (list) {
              flip.hidden = list.filter(function (d) { return d.kind === 'videoinput'; }).length < 2;
            }).catch(function () {});
          }
        }).catch(function (err) {
          var name = err && err.name;
          if (name === 'NotAllowedError' || name === 'SecurityError') {
            showMessage('fas fa-video-slash', 'The camera is blocked. Click the camera or lock icon in the address bar, choose Allow, then try again.');
          } else if (name === 'NotFoundError' || name === 'OverconstrainedError') {
            showMessage('fas fa-camera', 'No camera was found on this device.');
          } else if (name === 'NotReadableError') {
            showMessage('fas fa-camera', 'The camera is being used by another app. Close that app and try again.');
          } else {
            showMessage('fas fa-camera', 'The camera could not be started.');
          }
        });
      }

      shoot.addEventListener('click', function () {
        if (!stream || !video.videoWidth) return;
        var canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob(function (blob) {
          if (!blob) return;
          shotBlob = blob;
          still.src = URL.createObjectURL(blob);
          still.hidden = false;
          video.hidden = true;
          shoot.hidden = true;
          flip.hidden = true;
          retake.hidden = false;
          use.hidden = false;
          stop();
          use.focus();
        }, 'image/jpeg', 0.9);
      });
      retake.addEventListener('click', function () { shotBlob = null; start(); });
      flip.addEventListener('click', function () {
        facing = facing === 'environment' ? 'user' : 'environment';
        start();
      });
      use.addEventListener('click', function () {
        if (!shotBlob) return;
        var file = shotBlob;
        try {
          file = new File([shotBlob], 'photo-' + Date.now() + '.jpg', { type: 'image/jpeg' });
        } catch (e) {
          file.name = 'photo-' + Date.now() + '.jpg';
        }
        finish(file);
      });
      choose.addEventListener('click', function () {
        var input = fallbackInput;
        finish(null);
        if (input) input.click();
      });
      x.addEventListener('click', function () { finish(null); });
      overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) finish(null); });
      document.addEventListener('keydown', onKey, true);

      start();
    });
  }

  window.TMSCamera = { take: take, supported: supported };
})();
