/*
 * Technical Summary Report signing: draw or upload a signature, place it in your box
 * (drag + size), save. Saved signatures appear in the PDF at the same spot.
 */
(function () {
  'use strict';

  function ask(message) { return window.TmsDialog ? window.TmsDialog.confirm(message) : Promise.resolve(window.confirm(message)); }

  var paper = document.getElementById('sigPaper');
  if (!paper) return;

  var ticketId = paper.getAttribute('data-ticket-id');
  var base = paper.getAttribute('data-base') || '';
  var csrf = paper.getAttribute('data-csrf');
  var mySlots = (paper.getAttribute('data-slots') || '').split(',').filter(Boolean);
  var signed = (paper.getAttribute('data-signed') || '').split(',').filter(Boolean);
  var AREA_W = parseInt(paper.getAttribute('data-area-w'), 10) || 200;
  var AREA_H = parseInt(paper.getAttribute('data-area-h'), 10) || 64;
  var BASE_W = parseInt(paper.getAttribute('data-base-width'), 10) || 150;

  var modal = document.getElementById('sigModal');
  var pad = document.getElementById('sigPad');
  var ctx = pad.getContext('2d');
  var hint = document.getElementById('sigPadHint');
  var uploadRow = document.getElementById('sigUploadRow');
  var fileInput = document.getElementById('sigFile');
  var errorBox = document.getElementById('sigError');
  var placeBar = document.getElementById('sigPlace');
  var scaleInput = document.getElementById('sigScale');

  var current = { slot: null, area: null, img: null, data: null, x: 0, y: 0, scale: 100, hadImage: null };
  var drawing = false, last = null, hasInk = false;

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text) n.textContent = text;
    return n;
  }
  function showError(msg) { errorBox.textContent = msg; errorBox.hidden = !msg; }

  // ---------- pad ----------
  function resetPad() {
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, pad.width, pad.height);
    hasInk = false;
    hint.hidden = false;
    showError('');
  }
  function point(e) {
    var r = pad.getBoundingClientRect();
    return { x: (e.clientX - r.left) * (pad.width / r.width), y: (e.clientY - r.top) * (pad.height / r.height) };
  }
  pad.addEventListener('pointerdown', function (e) {
    if (!uploadRow.hidden) return;
    drawing = true;
    last = point(e);
    pad.setPointerCapture(e.pointerId);
    hint.hidden = true;
  });
  pad.addEventListener('pointermove', function (e) {
    if (!drawing) return;
    var p = point(e);
    ctx.strokeStyle = '#10216e';
    ctx.lineWidth = 2.8;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.beginPath();
    ctx.moveTo(last.x, last.y);
    ctx.quadraticCurveTo(last.x, last.y, (last.x + p.x) / 2, (last.y + p.y) / 2);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    last = p;
    hasInk = true;
  });
  function endStroke() { drawing = false; }
  pad.addEventListener('pointerup', endStroke);
  pad.addEventListener('pointercancel', endStroke);

  // Upload: fit the photo on the pad and turn the paper background white.
  fileInput.addEventListener('change', function () {
    var file = fileInput.files && fileInput.files[0];
    if (!file) return;
    if (!/^image\//.test(file.type)) { showError('Please choose an image file.'); return; }
    var url = URL.createObjectURL(file);
    var img = new Image();
    img.onload = function () {
      resetPad();
      var scale = Math.min((pad.width - 20) / img.naturalWidth, (pad.height - 20) / img.naturalHeight, 1);
      var w = img.naturalWidth * scale, h = img.naturalHeight * scale;
      ctx.drawImage(img, (pad.width - w) / 2, (pad.height - h) / 2, w, h);
      var data = ctx.getImageData(0, 0, pad.width, pad.height);
      var px = data.data;
      for (var i = 0; i < px.length; i += 4) {
        var lum = 0.299 * px[i] + 0.587 * px[i + 1] + 0.114 * px[i + 2];
        if (lum > 170) { px[i] = px[i + 1] = px[i + 2] = 255; }        // paper -> white
        else { px[i] = 16; px[i + 1] = 33; px[i + 2] = 110; }          // ink -> dark blue
      }
      ctx.putImageData(data, 0, 0);
      URL.revokeObjectURL(url);
      hasInk = true;
      hint.hidden = true;
    };
    img.onerror = function () { showError('That image could not be opened.'); };
    img.src = url;
  });

  // Crop to the ink and export a small JPEG (white background, works in every PDF).
  function exportSignature() {
    var data = ctx.getImageData(0, 0, pad.width, pad.height).data;
    var minX = pad.width, minY = pad.height, maxX = -1, maxY = -1;
    for (var y = 0; y < pad.height; y++) {
      for (var x = 0; x < pad.width; x++) {
        var i = (y * pad.width + x) * 4;
        if (data[i] < 200 || data[i + 1] < 200 || data[i + 2] < 200) {
          if (x < minX) minX = x; if (x > maxX) maxX = x;
          if (y < minY) minY = y; if (y > maxY) maxY = y;
        }
      }
    }
    if (maxX < 0 || (maxX - minX) < 15) return null;
    var padPx = 6;
    minX = Math.max(0, minX - padPx); minY = Math.max(0, minY - padPx);
    maxX = Math.min(pad.width - 1, maxX + padPx); maxY = Math.min(pad.height - 1, maxY + padPx);
    var out = document.createElement('canvas');
    out.width = maxX - minX + 1;
    out.height = maxY - minY + 1;
    var octx = out.getContext('2d');
    octx.fillStyle = '#ffffff';
    octx.fillRect(0, 0, out.width, out.height);
    octx.drawImage(pad, minX, minY, out.width, out.height, 0, 0, out.width, out.height);
    return out.toDataURL('image/jpeg', 0.92);
  }

  // ---------- modal ----------
  function openModal(slot, area) {
    current.slot = slot;
    current.area = area;
    resetPad();
    setTab('draw');
    fileInput.value = '';
    modal.hidden = false;
  }
  function closeModal() { modal.hidden = true; }
  function setTab(tab) {
    document.querySelectorAll('.sig-tab').forEach(function (b) {
      var on = b.getAttribute('data-tab') === tab;
      b.classList.toggle('active', on);
      b.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    uploadRow.hidden = tab !== 'upload';
    pad.style.cursor = tab === 'upload' ? 'default' : 'crosshair';
    hint.textContent = tab === 'upload' ? 'Choose a photo of your signature below' : 'Sign here with your mouse or finger';
  }
  document.querySelectorAll('.sig-tab').forEach(function (b) {
    b.addEventListener('click', function () { resetPad(); setTab(b.getAttribute('data-tab')); });
  });
  document.getElementById('sigClose').addEventListener('click', closeModal);
  document.getElementById('sigClear').addEventListener('click', function () { resetPad(); fileInput.value = ''; });
  document.getElementById('sigUse').addEventListener('click', function () {
    var data = hasInk ? exportSignature() : null;
    if (!data) { showError('Please draw your signature (or upload one) first.'); return; }
    closeModal();
    startPlacing(data);
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeModal(); });

  // ---------- placing ----------
  function widthFor(scale) { return Math.round(BASE_W * scale / 100); }
  function clampAndApply() {
    var w = widthFor(current.scale);
    current.x = Math.max(0, Math.min(AREA_W - w, current.x));
    current.y = Math.max(0, Math.min(AREA_H - 16, current.y));
    current.img.style.width = w + 'px';
    current.img.style.marginLeft = current.x + 'px';
    current.img.style.marginTop = current.y + 'px';
  }
  function startPlacing(data) {
    var area = current.area;
    var existing = area.querySelector('.sign-img');
    current.hadImage = existing ? existing.cloneNode(true) : null;
    area.querySelectorAll('.sign-img, .sig-here, .sig-mini').forEach(function (n) { n.remove(); });
    var img = el('img', 'sign-img');
    img.alt = 'Your signature';
    img.src = data;
    img.style.display = 'block';
    area.appendChild(img);
    current.img = img;
    current.data = data;
    current.scale = 100;
    current.x = Math.round((AREA_W - widthFor(100)) / 2);
    current.y = 4;
    scaleInput.value = '100';
    clampAndApply();
    area.classList.add('placing');
    placeBar.hidden = false;
    area.scrollIntoView({ behavior: 'smooth', block: 'center' });

    var start = null;
    img.addEventListener('pointerdown', function (e) {
      start = { px: e.clientX, py: e.clientY, x: current.x, y: current.y };
      img.setPointerCapture(e.pointerId);
      img.style.cursor = 'grabbing';
      e.preventDefault();
    });
    img.addEventListener('pointermove', function (e) {
      if (!start) return;
      current.x = start.x + Math.round(e.clientX - start.px);
      current.y = start.y + Math.round(e.clientY - start.py);
      clampAndApply();
    });
    function end() { start = null; img.style.cursor = ''; }
    img.addEventListener('pointerup', end);
    img.addEventListener('pointercancel', end);
  }
  scaleInput.addEventListener('input', function () {
    if (!current.img) return;
    current.scale = parseInt(scaleInput.value, 10) || 100;
    clampAndApply();
  });
  document.getElementById('sigCancel').addEventListener('click', function () { window.location.reload(); });

  function post(path, fields) {
    var form = new FormData();
    form.append('csrf_token', csrf);
    form.append('ticket_id', ticketId);
    Object.keys(fields).forEach(function (k) { form.append(k, fields[k]); });
    return fetch(base + path, { method: 'POST', body: form, credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return { success: false, message: 'Unexpected server reply.' }; }); });
  }
  var saveBtn = document.getElementById('sigSave');
  saveBtn.addEventListener('click', function () {
    if (!current.data) return;
    saveBtn.disabled = true;
    post('/tickets/signature/save', { slot: current.slot, image: current.data, x: current.x, y: current.y, scale: current.scale })
      .then(function (res) {
        if (!res.success) { alert(res.message || 'The signature was not saved.'); saveBtn.disabled = false; return; }
        window.location.reload();
      })
      .catch(function () { alert('Connection problem. Please try again.'); saveBtn.disabled = false; });
  });

  // ---------- boxes ----------
  mySlots.forEach(function (slot) {
    var cell = paper.querySelector('td[data-slot="' + slot + '"]');
    var area = cell && cell.querySelector('.sign-area');
    if (!area) return;
    area.classList.add('mine');
    if (signed.indexOf(slot) === -1) {
      var btn = el('button', 'sig-here', 'Sign here');
      btn.type = 'button';
      btn.addEventListener('click', function () { openModal(slot, area); });
      area.appendChild(btn);
    } else {
      var mini = el('div', 'sig-mini');
      var change = el('button', null, 'Change');
      change.type = 'button';
      change.addEventListener('click', function () { openModal(slot, area); });
      var remove = el('button', 'remove', 'Remove');
      remove.type = 'button';
      remove.addEventListener('click', function () {
        ask('Remove your signature from this report?').then(function (ok) {
          if (!ok) return;
          post('/tickets/signature/remove', { slot: slot }).then(function (res) {
            if (!res.success) { alert(res.message || 'Not removed.'); return; }
            window.location.reload();
          });
        });
      });
      mini.appendChild(change);
      mini.appendChild(remove);
      area.appendChild(mini);
    }
  });
})();
