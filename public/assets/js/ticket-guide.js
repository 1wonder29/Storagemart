/**
 * New-ticket helpers on every "Create Ticket" form:
 *  - a step-by-step guide (asked once per browser, replay with "How to file a ticket")
 *    that circles each field, types an example and shows the live preview;
 *  - a live hint when the description is too vague (the server enforces the same rule).
 */
(function () {
  'use strict';

  var subject = document.getElementById('subject');
  var description = document.getElementById('concern_details');
  var category = document.getElementById('category');
  var priority = document.getElementById('priority');
  if (!subject || !description) return;

  var STORAGE_KEY = 'tms_ticket_guide_seen';

  function storageGet(key) {
    try { return window.localStorage.getItem(key); } catch (e) { return null; }
  }
  function storageSet(key, value) {
    try { window.localStorage.setItem(key, value); } catch (e) { /* private mode */ }
  }

  /* ---------- Styles (kept here so every form page gets them) ---------- */
  var css = document.createElement('style');
  css.textContent = [
    '.tg-overlay{position:fixed;inset:0;z-index:2000;pointer-events:none}',
    '.tg-spot{position:absolute;border-radius:12px;box-shadow:0 0 0 9999px rgba(15,23,42,.55);',
    'transition:all .3s ease;pointer-events:none}',
    '.tg-ring{position:absolute;border:3px solid #f6c23e;border-radius:14px;animation:tgPulse 1.2s infinite;pointer-events:none}',
    '@keyframes tgPulse{0%{box-shadow:0 0 0 0 rgba(246,194,62,.7)}70%{box-shadow:0 0 0 14px rgba(246,194,62,0)}100%{box-shadow:0 0 0 0 rgba(246,194,62,0)}}',
    '.tg-tip{position:fixed;max-width:340px;background:#fff;color:#1f2937;border-radius:12px;padding:14px 16px;',
    'box-shadow:0 12px 30px rgba(0,0,0,.25);pointer-events:auto;font-size:.92rem;z-index:2001}',
    '.tg-tip h6{margin:0 0 6px;font-weight:800;color:#4e73df}',
    '.tg-tip .tg-step{font-size:.75rem;color:#6b7280;margin-bottom:4px}',
    '.tg-tip .tg-actions{display:flex;gap:6px;justify-content:flex-end;margin-top:10px}',
    '.tg-tip button{border:0;border-radius:8px;padding:5px 12px;font-weight:700;cursor:pointer}',
    '.tg-next{background:#4e73df;color:#fff}.tg-back{background:#e5e7eb;color:#374151}.tg-skip{background:transparent;color:#6b7280}',
    '.tg-ask{position:fixed;right:20px;bottom:90px;z-index:2002;max-width:320px;background:#fff;border-radius:14px;',
    'padding:16px;box-shadow:0 12px 30px rgba(0,0,0,.25);font-size:.92rem}',
    '.tg-ask h6{font-weight:800;color:#4e73df;margin:0 0 6px}',
    '.tg-replay{margin-left:8px}',
    '.tg-head-flex{display:flex !important;align-items:center;flex-wrap:wrap;gap:8px}',
    '.tg-top-btn{margin-left:auto;border:0;border-radius:999px;padding:5px 14px;font-weight:800;font-size:.8rem;background:#f26522;color:#fff;',
    'cursor:pointer;white-space:nowrap;animation:tgGlow 1.8s infinite;font-family:inherit}',
    '.tg-top-btn:hover{filter:brightness(1.07)}',
    '.tg-top-inline{display:flex;justify-content:flex-end;margin:0 0 10px}',
    '@keyframes tgGlow{0%{box-shadow:0 0 0 0 rgba(242,101,34,.6)}70%{box-shadow:0 0 0 10px rgba(242,101,34,0)}100%{box-shadow:0 0 0 0 rgba(242,101,34,0)}}',
    '@media (prefers-reduced-motion: reduce){.tg-top-btn{animation:none}}',
    '.ticket-specific-hint{font-weight:600}',
    'html.it-dark .tg-tip,html.it-dark .tg-ask{background:#1f2937;color:#e5e7eb}',
    'html.it-dark .tg-tip h6,html.it-dark .tg-ask h6{color:#93b4ff}',
    'html.it-dark .tg-tip .tg-step,html.it-dark .tg-skip{color:#9ca3af}',
    'html.it-dark .tg-back{background:#374151;color:#e5e7eb}'
  ].join('');
  document.head.appendChild(css);

  /* ---------- Live "be specific" hint ---------- */
  var hint = document.getElementById('ticketSpecificHint');
  var deviceCategories = /printer|desktop|laptop|cctv|network|pos|payment|biometric|access control|telephone|hardware/i;
  var brands = /\b(epson|brother|canon|hp|lenovo|asus|acer|dell|msi|samsung|xiaomi|huawei|tp-?link|cisco|mikrotik|hikvision|dahua|kedacom|ezviz|zkteco|panasonic|logitech|apc|secure|toshiba|ricoh|xerox|kyocera|pldt|globe|converge|chuwi|dere|nvision|viewsonic|aoc|philips|grandstream|yealink)\b/i;
  var modelToken = /\b(?!\d+(?:st|nd|rd|th)\b)(?=[a-z0-9-]*\d)(?=[a-z0-9-]*[a-z])[a-z0-9-]{3,}\b/i;
  var unitToken = /\b(camera|cam|channel|ch|unit|terminal|pc|computer|station|printer|phone|line|port|biometric|no)\.?\s*#?\s*\d+\b/i;
  var placeRe = /\b(front ?desk|counter|reception|lobby|office|room|floor|area|desk|station|cashier|admin|warehouse|unit|aisle|entrance|gate|door|stock ?room|pantry|conference|meeting|branch|bodega|taas|baba|ground|upstairs|downstairs|hallway|guard|server|rack|cabinet|table)\b/i;
  var oneLiner = /^(the |my |our |yung |ang )?(printer|computer|pc|laptop|desktop|internet|wifi|wi-fi|network|cctv|camera|email|outlook|phone|telephone|pos|terminal|biometric|system|monitor|mouse|keyboard|ups|router|scanner)( is| are)? ?(not working|not work|broken|down|sira|error|problem|issue|ayaw|hindi gumagana|no connection|offline|slow)$/i;
  var guideRunning = false;

  function hasAsset() {
    var asset = document.querySelector('[name="inventory_id"]');
    return !!(asset && asset.value);
  }

  function updateHint() {
    if (!hint || guideRunning) return;
    var details = description.value.trim();
    var text = (subject.value + ' ' + details).trim();
    var message = '';
    var normalized = details.toLowerCase().replace(/[^\p{L}\p{N}\s-]+/gu, ' ').replace(/\s+/g, ' ').trim();
    if (details && oneLiner.test(normalized)) {
      message = 'Too general. Say which device (brand/model), where it is, and what exactly happens.';
    } else if (details && details.split(/\s+/).length < 6) {
      message = 'Add more detail: which device or system, where, and what happens (at least one full sentence).';
    } else if (details && category && deviceCategories.test(category.value || '') && !hasAsset()) {
      var missing = [];
      if (!(brands.test(text) || modelToken.test(text) || unitToken.test(text))) missing.push('the brand/model');
      if (!placeRe.test(text)) missing.push('where it is');
      if (missing.length) message = 'Please add ' + missing.join(' and ') + ' - tickets without these are not accepted.';
    }
    hint.textContent = message ? '⚠ ' + message : '';
    hint.hidden = !message;
  }

  [subject, description, category].forEach(function (el) {
    if (!el) return;
    el.addEventListener('input', updateHint);
    el.addEventListener('change', updateHint);
  });

  /* ---------- Step-by-step guide ---------- */
  var preview = document.querySelector('.ticket-live-preview');
  var submit = document.querySelector('.ticket-form-actions button[type="submit"]');
  var employeePicker = document.getElementById('employee_search') || document.getElementById('employee_id_display');

  var steps = [];
  if (employeePicker && employeePicker.offsetParent !== null) {
    steps.push({ el: employeePicker, title: 'Choose the employee', text: 'Search and pick the employee this ticket is for.' });
  }
  steps.push({ el: subject, title: 'Type the subject here', text: 'A short headline: the device brand/model and the problem.',
    demo: 'Brother DCP-L2540DW at the front desk - Paper Jam' });
  steps.push({ el: description, title: 'Describe the problem here', text: 'Where it is, what happens, any error message, and since when. Vague text like "Printer is not working" is not accepted.',
    demo: 'The Brother DCP-L2540DW printer at the front desk shows "Paper Jam" even with no paper inside. It stopped printing receipts since 9 AM.' });
  if (category) steps.push({ el: category, title: 'Pick a category', text: 'Choose the closest match so the ticket reaches the right IT person.' });
  if (priority) steps.push({ el: priority, title: 'Set the priority', text: 'Use High for urgent problems and Critical only for outages that stop operations.' });
  if (preview) steps.push({ el: preview, title: 'Preview here', text: 'This is how IT will see your ticket. Check it before sending.' });
  if (submit) steps.push({ el: submit, title: 'Send it', text: 'Click here to file the ticket. IT is assigned automatically and you can chat with them from the chat bubble at the bottom.' });

  var overlay, spot, ring, tip, index = 0, saved = null, typingTimer = null;

  function fire(el) {
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function typeInto(el, text) {
    clearInterval(typingTimer);
    el.value = '';
    var i = 0;
    typingTimer = setInterval(function () {
      el.value = text.slice(0, ++i);
      fire(el);
      if (i >= text.length) clearInterval(typingTimer);
    }, 25);
  }

  function positionAt(el) {
    var r = el.getBoundingClientRect();
    var pad = 6;
    [spot, ring].forEach(function (box) {
      box.style.top = (r.top - pad) + 'px';
      box.style.left = (r.left - pad) + 'px';
      box.style.width = (r.width + pad * 2) + 'px';
      box.style.height = (r.height + pad * 2) + 'px';
    });
    // Never cover the field: use the panel's real size and put it fully below or above it.
    var tipH = tip.offsetHeight || 150;
    var tipW = tip.offsetWidth || 340;
    var gap = 16 + pad;
    var top;
    if (r.bottom + gap + tipH <= window.innerHeight - 8) {
      top = r.bottom + gap;
    } else if (r.top - gap - tipH >= 8) {
      top = r.top - gap - tipH;
    } else if (r.right + gap + tipW <= window.innerWidth - 8) {
      // Tall field (e.g. the description box): beside it.
      top = Math.max(8, Math.min(r.top, window.innerHeight - tipH - 8));
      tip.style.left = (r.right + gap) + 'px';
      tip.style.top = top + 'px';
      return;
    } else {
      top = Math.max(8, window.innerHeight - tipH - 8);
    }
    tip.style.top = top + 'px';
    tip.style.left = Math.max(10, Math.min(r.left, window.innerWidth - tipW - 10)) + 'px';
  }

  function show(i) {
    index = i;
    var step = steps[i];
    step.el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    tip.innerHTML = '<div class="tg-step">Step ' + (i + 1) + ' of ' + steps.length + '</div>' +
      '<h6></h6><div class="tg-text"></div><div class="tg-actions">' +
      '<button type="button" class="tg-skip">Skip</button>' +
      (i > 0 ? '<button type="button" class="tg-back">Back</button>' : '') +
      '<button type="button" class="tg-next">' + (i === steps.length - 1 ? 'Done' : 'Next') + '</button></div>';
    tip.querySelector('h6').textContent = step.title;
    tip.querySelector('.tg-text').textContent = step.text;
    tip.querySelector('.tg-skip').onclick = finish;
    tip.querySelector('.tg-next').onclick = function () { i === steps.length - 1 ? finish() : show(i + 1); };
    var back = tip.querySelector('.tg-back');
    if (back) back.onclick = function () { show(i - 1); };
    // Smooth scrolling takes a moment: follow the field until it stops moving, then place the panel.
    settle(step.el, i);
    if (step.demo) typeInto(step.el, step.demo);
  }

  function settle(el, stepIndex) {
    var lastTop = null, still = 0, tries = 0;
    (function check() {
      if (!guideRunning || index !== stepIndex) return;
      var top = Math.round(el.getBoundingClientRect().top);
      positionAt(el);
      still = top === lastTop ? still + 1 : 0;
      lastTop = top;
      if (still < 3 && ++tries < 40) setTimeout(check, 80);
    })();
  }

  function start() {
    if (guideRunning || !steps.length) return;
    guideRunning = true;
    if (hint) hint.hidden = true;
    saved = { subject: subject.value, description: description.value };
    overlay = document.createElement('div');
    overlay.className = 'tg-overlay';
    spot = document.createElement('div'); spot.className = 'tg-spot';
    ring = document.createElement('div'); ring.className = 'tg-ring';
    tip = document.createElement('div'); tip.className = 'tg-tip';
    overlay.appendChild(spot); overlay.appendChild(ring);
    document.body.appendChild(overlay);
    document.body.appendChild(tip);
    window.addEventListener('resize', reposition);
    window.addEventListener('scroll', reposition, true);
    show(0);
  }

  function reposition() {
    if (guideRunning && steps[index]) positionAt(steps[index].el);
  }

  function finish() {
    clearInterval(typingTimer);
    guideRunning = false;
    storageSet(STORAGE_KEY, '1');
    if (overlay) overlay.remove();
    if (tip) tip.remove();
    window.removeEventListener('resize', reposition);
    window.removeEventListener('scroll', reposition, true);
    // Put back what the user had typed before the demo.
    if (saved) {
      subject.value = saved.subject; fire(subject);
      description.value = saved.description; fire(description);
    }
    updateHint();
  }

  // Small highlighted "How to file a ticket" button in the form's header bar (top of the form).
  var ticketForm = subject && subject.form;
  if (ticketForm) {
    var topBtn = document.createElement('button');
    topBtn.type = 'button';
    topBtn.className = 'tg-top-btn';
    topBtn.title = 'Step-by-step guide: where to type, what to write and how to send it';
    topBtn.innerHTML = '<i class="fas fa-play-circle"></i> How to file a ticket';
    topBtn.addEventListener('click', function () {
      var askBox = document.querySelector('.tg-ask');
      if (askBox) askBox.remove();
      start();
    });
    var card = ticketForm.closest('.card');
    var header = card && card.querySelector('.card-header');
    if (header && card.contains(ticketForm)) {
      header.classList.add('tg-head-flex');
      header.appendChild(topBtn);
    } else {
      var row = document.createElement('div');
      row.className = 'tg-top-inline';
      row.appendChild(topBtn);
      ticketForm.insertBefore(row, ticketForm.firstElementChild);
    }
  }

  // "How to file a ticket" replay button next to Create / Cancel.
  var actions = document.querySelector('.ticket-form-actions');
  if (actions) {
    var replay = document.createElement('button');
    replay.type = 'button';
    replay.className = 'btn btn-outline-info tg-replay';
    replay.innerHTML = '<i class="fas fa-question-circle"></i> How to file a ticket';
    replay.addEventListener('click', start);
    actions.appendChild(replay);
  }

  // First visit: ask whether they want the guide.
  if (!storageGet(STORAGE_KEY)) {
    var ask = document.createElement('div');
    ask.className = 'tg-ask';
    ask.innerHTML = '<h6><i class="fas fa-hands-helping"></i> New here?</h6>' +
      '<div>Would you like a quick step-by-step guide on how to file a ticket?</div>' +
      '<div class="tg-actions" style="display:flex;gap:6px;justify-content:flex-end;margin-top:10px">' +
      '<button type="button" class="btn btn-sm btn-light tg-no">No, thanks</button>' +
      '<button type="button" class="btn btn-sm btn-primary tg-yes">Show me how</button></div>';
    document.body.appendChild(ask);
    ask.querySelector('.tg-no').onclick = function () { storageSet(STORAGE_KEY, '1'); ask.remove(); };
    ask.querySelector('.tg-yes').onclick = function () { ask.remove(); start(); };
  }

  updateHint();
})();
