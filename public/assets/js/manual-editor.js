/*
 * Troubleshooting guide maker (app/Views/system/manuals.php).
 *  - Tabs: Create / edit, All guides, Which guide is sent?
 *  - 4 steps: Basics, The problem, The fix, Finish, with Back / Next.
 *  - Step builder: steps, section titles and info pages as cards with a photo each, moved with the
 *    arrows. It writes the same "steps" text as before (one line per STEP, "# " section, "## Heading :: text"
 *    page, " || " new line), and step_photo_map so saved photos follow their step when steps move.
 *  - Live preview of every slide; clicking a slide opens the box it comes from.
 *  - "Start from an example", keyword suggestions, a checklist, and "How to create a guide".
 * Config: window.TMS_MANUAL_EDITOR (base, isNew, steps, photos, actionDefault, logo, categories).
 */
(function () {
  'use strict';
  var cfg = window.TMS_MANUAL_EDITOR || {};
  var form = document.getElementById('manualForm');

  function $(id) { return document.getElementById(id); }
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined && text !== null) n.textContent = text;
    return n;
  }
  function icon(cls) { var i = el('i', cls); i.setAttribute('aria-hidden', 'true'); return i; }

  // ---------------------------------------------------------------- tabs
  var tabs = document.querySelectorAll('.me-tab');
  function openTab(name) {
    tabs.forEach(function (t) { t.classList.toggle('on', t.getAttribute('data-tab') === name); t.setAttribute('aria-selected', t.getAttribute('data-tab') === name); });
    document.querySelectorAll('.me-pane').forEach(function (p) { p.hidden = p.getAttribute('data-pane') !== name; });
    if (name === 'create') renderPreview();
  }
  tabs.forEach(function (t) { t.addEventListener('click', function () { openTab(t.getAttribute('data-tab')); }); });

  // Guides list filter.
  var filter = $('guideFilter');
  if (filter) {
    filter.addEventListener('input', function () {
      var words = filter.value.toLowerCase().split(/\s+/).filter(Boolean);
      document.querySelectorAll('.guide-group').forEach(function (group) {
        var any = false;
        group.querySelectorAll('.guide-row').forEach(function (row) {
          var text = row.getAttribute('data-search');
          var ok = words.every(function (w) { return text.indexOf(w) !== -1; });
          row.style.display = ok ? '' : 'none';
          if (ok) any = true;
        });
        group.style.display = any ? '' : 'none';
      });
    });
  }
  if (!form) return;

  // ---------------------------------------------------------------- wizard
  var current = 1;
  var LAST = 4;
  var stepper = $('stepper');
  var back = $('backBtn'), next = $('nextBtn'), progress = $('progressText');
  var NAMES = ['', 'Basics', 'The problem', 'The fix', 'Finish'];
  function go(n) {
    current = Math.max(1, Math.min(LAST, n));
    form.querySelectorAll('.me-step').forEach(function (s) { s.hidden = Number(s.getAttribute('data-step')) !== current; });
    stepper.querySelectorAll('li').forEach(function (li) {
      var k = Number(li.getAttribute('data-go'));
      li.classList.toggle('on', k === current);
      li.classList.toggle('done', k < current);
    });
    back.disabled = current === 1;
    next.hidden = current === LAST;
    next.innerHTML = '';
    next.appendChild(document.createTextNode('Next: ' + (NAMES[current + 1] || '') + ' '));
    next.appendChild(icon('fas fa-arrow-right'));
    progress.textContent = 'Step ' + current + ' of ' + LAST + ' · ' + NAMES[current];
    if (current === LAST) updateChecklist();
  }
  stepper.querySelectorAll('li').forEach(function (li) { li.addEventListener('click', function () { go(Number(li.getAttribute('data-go'))); }); });
  back.addEventListener('click', function () { go(current - 1); });
  next.addEventListener('click', function () {
    if (current === 1 && !$('m_title').value.trim()) { $('m_title').focus(); $('m_title').classList.add('is-invalid'); return; }
    go(current + 1);
    var card = $('manual-form');
    if (card.getBoundingClientRect().top < 0) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
  $('m_title').addEventListener('input', function () { this.classList.remove('is-invalid'); });

  // Group chips.
  document.querySelectorAll('.js-group').forEach(function (chip) {
    chip.addEventListener('click', function () { $('m_group').value = chip.getAttribute('data-value'); markGroup(); });
  });
  function markGroup() {
    var v = $('m_group').value.trim().toLowerCase();
    document.querySelectorAll('.js-group').forEach(function (c) { c.classList.toggle('on', c.getAttribute('data-value').toLowerCase() === v); });
  }
  $('m_group').addEventListener('input', markGroup);
  markGroup();

  // Who can open it.
  function syncAudience() {
    var mode = (form.querySelector('input[name="audience_mode"]:checked') || {}).value;
    $('audienceRoles').hidden = mode !== 'some';
  }
  form.querySelectorAll('input[name="audience_mode"]').forEach(function (r) { r.addEventListener('change', syncAudience); });
  var defaultSwitch = $('m_default');
  if (defaultSwitch) {
    defaultSwitch.addEventListener('change', function () {
      if (defaultSwitch.checked) form.querySelector('input[name="audience_mode"][value="all"]').checked = true;
      syncAudience();
    });
  }

  $('actionReset').addEventListener('click', function () { $('m_action').value = cfg.actionDefault || ''; renderPreview(); });

  // ---------------------------------------------------------------- step builder
  var items = [];
  var list = $('stepList');
  var textMode = false;

  function parse(text) {
    var out = [];
    String(text || '').split(/\r?\n/).forEach(function (line) {
      line = line.trim();
      if (!line) return;
      if (line.indexOf('##') === 0) {
        var rest = line.slice(2).trim();
        var cut = rest.indexOf('::');
        out.push({ type: 'page', heading: cut === -1 ? rest : rest.slice(0, cut).trim(), text: cut === -1 ? '' : toLines(rest.slice(cut + 2).trim()) });
      } else if (line.charAt(0) === '#') {
        out.push({ type: 'section', text: line.slice(1).trim() });
      } else {
        out.push({ type: 'step', text: toLines(line) });
      }
    });
    return out;
  }
  function toLines(text) { return text.replace(/\s*\|\|\s*/g, '\n'); }
  function oneLine(text) { return String(text || '').trim().split(/\r?\n/).map(function (s) { return s.trim(); }).filter(Boolean).join(' || '); }
  function serialize() {
    return items.map(function (it) {
      if (it.type === 'section') return it.text.trim() ? '# ' + oneLine(it.text) : '';
      if (it.type === 'page') return (it.heading.trim() || it.text.trim()) ? '## ' + oneLine(it.heading) + ' :: ' + oneLine(it.text) : '';
      return oneLine(it.text);
    }).filter(Boolean).join('\n');
  }

  // Saved photos belong to the n-th STEP line.
  (function load() {
    items = parse(cfg.steps);
    var n = 0;
    items.forEach(function (it) {
      if (it.type !== 'step') return;
      n++;
      if (cfg.photos && cfg.photos[n]) { it.photoUrl = cfg.photos[n]; it.oldIndex = n; }
    });
  })();

  function stepNumbers() {
    var number = 0;
    return items.map(function (it) {
      if (it.type === 'section') { number = 0; return null; }
      if (it.type === 'step') { number++; return number; }
      return null;
    });
  }

  function render(focusIndex) {
    list.textContent = '';
    var numbers = stepNumbers();
    items.forEach(function (it, i) {
      var card = el('div', 'me-item me-type-' + it.type);
      var head = el('div', 'me-item-head');
      head.appendChild(el('span', 'me-badge', it.type === 'step' ? 'STEP ' + numbers[i] : it.type === 'section' ? 'SECTION TITLE' : 'INFO PAGE'));
      var tools = el('div', 'me-tools');
      [['fas fa-arrow-up', 'Move up', -1], ['fas fa-arrow-down', 'Move down', 1]].forEach(function (t) {
        var b = el('button');
        b.type = 'button';
        b.title = t[1];
        b.setAttribute('aria-label', t[1]);
        b.appendChild(icon(t[0]));
        b.disabled = (t[2] < 0 && i === 0) || (t[2] > 0 && i === items.length - 1);
        b.addEventListener('click', function () { move(i, t[2]); });
        tools.appendChild(b);
      });
      var del = el('button', 'del');
      del.type = 'button';
      del.title = 'Remove';
      del.setAttribute('aria-label', 'Remove');
      del.appendChild(icon('fas fa-trash'));
      del.addEventListener('click', function () {
        if ((it.text || it.heading || it.photoUrl || it.file) && !window.confirm('Remove this ' + (it.type === 'step' ? 'step' : it.type === 'section' ? 'section title' : 'page') + '?')) return;
        items.splice(i, 1);
        render();
        renderPreview();
      });
      tools.appendChild(del);
      head.appendChild(tools);
      card.appendChild(head);

      if (it.type === 'page') {
        var heading = el('input', 'form-control form-control-sm mb-1');
        heading.type = 'text';
        heading.placeholder = 'Page heading, e.g. What is LOS?';
        heading.value = it.heading || '';
        heading.addEventListener('input', function () { it.heading = heading.value; schedulePreview(); });
        card.appendChild(heading);
      }
      var area;
      if (it.type === 'section') {
        area = el('input', 'form-control form-control-sm');
        area.type = 'text';
        area.placeholder = 'Section title, e.g. 1. Jam in the paper tray';
      } else {
        area = el('textarea', 'form-control form-control-sm');
        area.rows = 2;
        area.placeholder = it.type === 'step' ? 'What to do, e.g. Pull the paper tray completely out of the printer.' : 'The page text. Start a line with "- " for a bullet.';
      }
      area.value = it.text || '';
      area.setAttribute('aria-label', it.type === 'step' ? 'Step ' + numbers[i] : it.type === 'section' ? 'Section title' : 'Page text');
      area.addEventListener('input', function () { it.text = area.value; schedulePreview(); });
      card.appendChild(area);

      if (it.type === 'step') card.appendChild(photoRow(it));
      list.appendChild(card);
      if (focusIndex === i) setTimeout(function () { (it.type === 'page' ? card.querySelector('input') : area).focus(); }, 30);
    });
    $('stepEmpty').hidden = items.length > 0 || textMode;
  }

  function photoRow(it) {
    var row = el('div', 'me-photo');
    if (it.previewUrl || it.photoUrl) {
      var img = el('img');
      img.src = it.previewUrl || it.photoUrl;
      img.alt = 'Step photo';
      row.appendChild(img);
    }
    if (!it.input) {
      it.input = el('input');
      it.input.type = 'file';
      it.input.accept = 'image/jpeg,image/png';
      it.input.addEventListener('change', function () {
        var f = it.input.files && it.input.files[0];
        if (!f) return;
        if (f.size > 8 * 1024 * 1024) { window.alert('The photo is too large (max 8 MB).'); it.input.value = ''; return; }
        if (it.previewUrl) URL.revokeObjectURL(it.previewUrl);
        it.previewUrl = URL.createObjectURL(f);
        render();
        renderPreview();
      });
    }
    var pick = el('label', 'btn btn-sm btn-outline-primary');
    pick.appendChild(icon('fas fa-camera mr-1'));
    pick.appendChild(document.createTextNode(it.previewUrl || it.photoUrl ? ' Change photo' : ' Add photo'));
    pick.appendChild(it.input);
    row.appendChild(pick);
    if (it.previewUrl || it.photoUrl) {
      var rm = el('button', 'btn btn-sm btn-link text-danger p-0', 'Remove photo');
      rm.type = 'button';
      rm.addEventListener('click', function () {
        it.input.value = '';
        if (it.previewUrl) URL.revokeObjectURL(it.previewUrl);
        it.previewUrl = null;
        it.photoUrl = null;
        it.oldIndex = null;
        render();
        renderPreview();
      });
      row.appendChild(rm);
    } else {
      row.appendChild(el('span', null, 'Optional: JPG or PNG, shown on the left of the STEP slide.'));
    }
    return row;
  }

  function move(i, dir) {
    var j = i + dir;
    if (j < 0 || j >= items.length) return;
    var tmp = items[i];
    items[i] = items[j];
    items[j] = tmp;
    render();
    renderPreview();
  }

  document.querySelectorAll('[data-add]').forEach(function (b) {
    b.addEventListener('click', function () {
      if (textMode) setTextMode(false);
      var type = b.getAttribute('data-add');
      items.push(type === 'page' ? { type: 'page', heading: '', text: '' } : { type: type, text: '' });
      render(items.length - 1);
      renderPreview();
    });
  });

  // "Edit as text" for people who like the old way; photos stay with the n-th step.
  function setTextMode(on) {
    if (on === textMode) return;
    if (on) {
      $('m_steps').value = serialize();
    } else {
      var oldSteps = items.filter(function (it) { return it.type === 'step'; });
      items = parse($('m_steps').value);
      var n = 0;
      items.forEach(function (it) {
        if (it.type !== 'step') return;
        var prev = oldSteps[n++];
        if (prev) { it.photoUrl = prev.photoUrl; it.oldIndex = prev.oldIndex; it.input = prev.input; it.previewUrl = prev.previewUrl; }
      });
    }
    textMode = on;
    $('textMode').hidden = !on;
    list.hidden = on;
    document.querySelector('.me-add').hidden = on;
    $('textModeBtn').innerHTML = '';
    $('textModeBtn').appendChild(icon(on ? 'fas fa-th-list mr-1' : 'fas fa-code mr-1'));
    $('textModeBtn').appendChild(document.createTextNode(on ? 'Back to the step builder' : 'Edit as text'));
    render();
    renderPreview();
  }
  $('textModeBtn').addEventListener('click', function () { setTextMode(!textMode); });
  $('m_steps').addEventListener('input', schedulePreview);

  // ---------------------------------------------------------------- live preview
  var slidesBox = $('slides');
  var previewTimer = null;
  function schedulePreview() { clearTimeout(previewTimer); previewTimer = setTimeout(renderPreview, 150); }
  function val(id) { var n = $(id); return n ? n.value.trim() : ''; }
  function currentItems() {
    if (!textMode) return items;
    return parse($('m_steps').value);
  }
  function textBlock(text, bullets) {
    var box = el('div', 'tx');
    var lines = String(text).split(/\r?\n|\s*\|\|\s*/).map(function (s) { return s.trim(); }).filter(Boolean);
    var ul = null;
    lines.forEach(function (line) {
      if (bullets || /^[-•]\s*/.test(line)) {
        if (!ul) { ul = el('ul'); box.appendChild(ul); }
        ul.appendChild(el('li', null, line.replace(/^[-•]\s*/, '')));
      } else {
        ul = null;
        box.appendChild(el('div', null, line));
      }
    });
    return box;
  }
  function frame(kind, page, target) {
    var s = el('div', 'me-slide ' + kind);
    s.tabIndex = 0;
    s.setAttribute('role', 'button');
    if (kind !== 'section' && kind !== 'thanks') {
      var logo = el('img', 'logo');
      logo.src = cfg.logo;
      logo.alt = '';
      s.appendChild(logo);
      s.appendChild(el('span', 'k', 'IT DEPARTMENT MANUALS'));
      s.appendChild(el('span', 'line'));
      s.appendChild(el('span', 'pg', 'Page ' + ('0' + page).slice(-2)));
    }
    if (target) {
      var open = function () { jump(target); };
      s.addEventListener('click', open);
      s.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
    }
    return s;
  }
  function renderPreview() {
    if (!slidesBox) return;
    slidesBox.textContent = '';
    var page = 0;
    var add = function (s) { slidesBox.appendChild(s); };
    // Cover
    var cover = frame('cover', ++page, { step: 1, id: 'm_title' });
    cover.appendChild(el('span', 'pink'));
    cover.appendChild(el('div', 't', val('m_title') || 'Your guide title'));
    cover.appendChild(el('span', 'tag', 'Cover'));
    add(cover);
    [['m_intro', 'Introduction', 2, false], ['m_issue', 'Main Issue', 2, false], ['m_factors', 'Factors', 2, true],
      ['m_action', 'Action', 3, false], ['m_solution', 'Solution', 3, false]].forEach(function (f) {
      var text = val(f[0]);
      if (f[0] === 'm_intro' && !text) text = val('m_desc');
      if (!text) return;
      var s = frame('text', ++page, { step: f[2], id: f[0] });
      s.appendChild(el('div', 'h', f[1]));
      s.appendChild(textBlock(text, f[3]));
      add(s);
    });
    var number = 0;
    var stepIndex = 0;
    currentItems().forEach(function (it, i) {
      var target = { step: 3, item: i };
      if (it.type === 'section') {
        if (!String(it.text).trim()) return;
        number = 0;
        var sec = frame('section', ++page, target);
        sec.appendChild(el('div', 't', it.text));
        add(sec);
      } else if (it.type === 'page') {
        if (!String(it.heading).trim() && !String(it.text).trim()) return;
        var pg = frame('text', ++page, target);
        pg.appendChild(el('div', 'h', it.heading || 'Heading'));
        pg.appendChild(textBlock(it.text, false));
        add(pg);
      } else {
        if (!String(it.text).trim()) return;
        number++;
        stepIndex++;
        var photo = it.previewUrl || it.photoUrl;
        var st = frame('step' + (photo ? ' has-photo' : ''), ++page, target);
        st.appendChild(el('div', 'h', 'STEP ' + number));
        if (photo) { var img = el('img', 'ph'); img.src = photo; img.alt = ''; st.appendChild(img); }
        st.appendChild(textBlock(it.text, false));
        add(st);
      }
    });
    if (!stepIndex && !textMode) {
      var hint = frame('text empty-hint', ++page, { step: 3, id: null, add: true });
      hint.appendChild(el('div', 'h', 'STEP 1'));
      hint.appendChild(el('div', 'tx', 'Add your steps in "The fix" (step 3).'));
      add(hint);
    }
    if (val('m_note')) {
      var note = frame('text', ++page, { step: 4, id: 'm_note' });
      note.appendChild(el('div', 'h', 'Note'));
      note.appendChild(textBlock(val('m_note'), false));
      add(note);
    }
    var thanks = frame('thanks', ++page, null);
    thanks.appendChild(el('div', 't', 'THANK YOU'));
    add(thanks);
    $('slideCount').textContent = String(slidesBox.children.length);
  }
  function jump(target) {
    go(target.step);
    var node = null;
    if (target.id) node = $(target.id);
    else if (target.item !== undefined && !textMode) node = list.children[target.item] && list.children[target.item].querySelector('input, textarea');
    else if (textMode) node = $('m_steps');
    else if (target.add) node = document.querySelector('[data-add="step"]');
    if (!node) return;
    node.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(function () { node.focus({ preventScroll: true }); }, 250);
    var field = node.closest('.me-field, .me-item');
    if (field) { field.classList.add('me-focus'); setTimeout(function () { field.classList.remove('me-focus'); }, 1600); }
  }
  form.querySelectorAll('[data-preview], #m_desc').forEach(function (n) { n.addEventListener('input', schedulePreview); });

  // ---------------------------------------------------------------- finish: when is it sent, checklist
  var STOP = ['the', 'and', 'for', 'with', 'not', 'cannot', "can't", 'won\'t', 'how', 'to', 'of', 'or', 'on', 'in', 'is', 'a', 'an', 'issue', 'issues', 'problem', 'error', 'my', 'your'];
  function suggestKeywords() {
    var title = val('m_title').toLowerCase().replace(/\([^)]*\)/g, ' ').replace(/[^a-z0-9&'\s\/-]/g, ' ');
    var words = title.split(/[\s\/]+/).filter(function (w) { return w.length > 2 && STOP.indexOf(w) === -1; });
    var out = [];
    var push = function (k) { k = k.trim(); if (k && out.indexOf(k) === -1) out.push(k); };
    for (var i = 0; i < words.length - 1; i++) push(words[i] + ' ' + words[i + 1]);
    words.forEach(function (w) { if (w.length > 3) push(w); });
    var existing = val('m_kw').split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    $('m_kw').value = existing.concat(out.filter(function (k) { return existing.indexOf(k) === -1; })).slice(0, 12).join(', ');
    updateSentence();
  }
  $('suggestKw').addEventListener('click', suggestKeywords);
  function checkedCategories() {
    return Array.prototype.map.call(form.querySelectorAll('input[name="categories[]"]:checked'), function (c) { return c.value; });
  }
  function updateSentence() {
    var cats = checkedCategories();
    var kws = val('m_kw').split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    var mode = (form.querySelector('input[name="audience_mode"]:checked') || {}).value;
    var box = $('matchSentence');
    box.textContent = '';
    if (mode === 'it') { box.textContent = 'IT and Admin only: never sent in a ticket chat. It is in the library for IT.'; return; }
    if (!cats.length && !kws.length) {
      box.textContent = 'Not sent automatically yet: tick a category or add keywords. People can still open it in the library.';
      return;
    }
    box.appendChild(document.createTextNode('Sent in the chat of tickets '));
    if (cats.length) { box.appendChild(document.createTextNode('in ')); box.appendChild(el('strong', null, cats.join(', '))); }
    if (cats.length && kws.length) box.appendChild(document.createTextNode(', or '));
    if (kws.length) { box.appendChild(document.createTextNode('that mention ')); box.appendChild(el('strong', null, kws.slice(0, 8).join(', '))); }
    box.appendChild(document.createTextNode('. Words in the subject count more than the category.'));
  }
  form.querySelectorAll('input[name="categories[]"], input[name="audience_mode"]').forEach(function (c) { c.addEventListener('change', updateSentence); });
  $('m_kw').addEventListener('input', updateSentence);

  function updateChecklist() {
    var ul = $('checklist');
    ul.textContent = '';
    var stepsCount = (textMode ? parse($('m_steps').value) : items).filter(function (it) { return it.type === 'step' && String(it.text).trim(); }).length;
    var hasText = val('m_intro') || val('m_issue') || val('m_solution');
    var hasFile = ($('m_file') && $('m_file').files && $('m_file').files.length) || val('m_url');
    var rows = [
      [val('m_title') ? 'ok' : 'bad', val('m_title') ? 'Title: ' + val('m_title') : 'Add a title (step 1).'],
      [stepsCount ? 'ok' : (hasText || hasFile ? 'warn' : 'bad'), stepsCount ? stepsCount + ' step' + (stepsCount === 1 ? '' : 's') : (hasText || hasFile ? 'No steps yet (the other slides are still made).' : 'Add steps or the other slide texts (steps 2 and 3).')],
      [checkedCategories().length || val('m_kw') ? 'ok' : 'warn', checkedCategories().length || val('m_kw') ? 'It will be sent for matching tickets.' : 'No category or keyword: it will not be sent automatically.'],
      ['ok', slidesBox.children.length + ' slides and a PowerPoint will be made.'],
    ];
    rows.forEach(function (r) {
      var li = el('li', r[0]);
      li.appendChild(icon('fas ' + (r[0] === 'ok' ? 'fa-check-circle' : r[0] === 'warn' ? 'fa-exclamation-circle' : 'fa-times-circle')));
      li.appendChild(document.createTextNode(' ' + r[1]));
      ul.appendChild(li);
    });
  }

  // ---------------------------------------------------------------- example
  var exampleBtn = $('exampleBtn');
  if (exampleBtn) {
    exampleBtn.addEventListener('click', function () {
      var filled = ['m_title', 'm_intro', 'm_issue', 'm_factors', 'm_solution', 'm_note'].some(function (id) { return val(id); }) || items.length;
      if (filled && !window.confirm('Replace what you typed with the example?')) return;
      $('m_title').value = 'Printer Paper Jam (Brother DCP-L2540DW)';
      $('m_group').value = 'Printer Issues';
      $('m_intro').value = 'In this procedure the facilitator can clear a paper jam on the Brother DCP-L2540DW without waiting for IT.';
      $('m_issue').value = 'The printer stops and the screen shows "Paper Jam" or "Jam Inside".';
      $('m_factors').value = 'Paper tray is overfilled\nCurled, damp or wrinkled paper\nWrong paper size or type\nA small torn piece is left inside';
      $('m_action').value = cfg.actionDefault || '';
      $('m_solution').value = 'The facilitator can remove the jammed paper by following the steps. If the error is still there after 2 tries, report it to IT.';
      $('m_note').value = 'Do not use sharp objects to pull the paper.\n- Do not touch the parts at the back: they can be hot.';
      $('m_kw').value = 'paper jam, jammed, jam inside, stuck paper';
      items = [
        { type: 'section', text: '1. Jam in the paper tray' },
        { type: 'step', text: 'Turn off the printer and wait 10 minutes so the inside cools down.' },
        { type: 'step', text: 'Pull the paper tray completely out of the printer.' },
        { type: 'step', text: 'Slowly pull out the jammed paper with both hands.' },
        { type: 'section', text: '2. Jam inside the printer' },
        { type: 'step', text: 'Open the front cover and take out the drum and toner unit.' },
        { type: 'step', text: 'Remove the jammed paper, put the drum and toner unit back and close the cover.' },
        { type: 'page', heading: 'Prevent it next time', text: '- Fill the tray only up to the line\n- Fan the paper before loading it' },
      ];
      form.querySelectorAll('input[name="categories[]"]').forEach(function (c) { c.checked = c.value === 'Printer Issues'; });
      if (textMode) setTextMode(false);
      markGroup();
      render();
      renderPreview();
      updateSentence();
    });
  }

  // ---------------------------------------------------------------- submit
  form.addEventListener('submit', function (e) {
    if (textMode) setTextMode(false);
    if (!val('m_title')) {
      e.preventDefault();
      go(1);
      $('m_title').classList.add('is-invalid');
      $('m_title').focus();
      return;
    }
    $('m_steps_hidden').value = serialize();
    // Photos: the new upload of the k-th step is step_photo[k]; saved photos follow their step.
    var map = {};
    var k = 0;
    items.forEach(function (it) {
      if (it.type !== 'step' || !String(it.text).trim()) {
        if (it.input) it.input.removeAttribute('name');
        return;
      }
      k++;
      if (it.input && it.input.files && it.input.files.length) it.input.name = 'step_photo[' + k + ']';
      else if (it.input) it.input.removeAttribute('name');
      if (it.oldIndex) map[k] = it.oldIndex;
    });
    $('stepPhotoMap').value = JSON.stringify(map);
  });

  // ---------------------------------------------------------------- "How to create a guide"
  var TOUR = [
    { el: '#tabCreate', title: 'Create a guide', text: 'Click "Create a guide". A guide is a set of slides that shows how to fix one problem, step by step.' },
    { el: '#stepper', step: 1, title: '4 easy steps', text: 'Basics, The problem, The fix and Finish. Click Next, or click a number to jump. You can save at any time.' },
    { el: '#wz-title', step: 1, title: '1. Title', text: 'Name the problem the way users say it, with the device model, e.g. "Printer Paper Jam (Brother DCP-L2540DW)".' },
    { el: '#wz-group', step: 1, title: 'Group', text: 'Pick the library folder (Printer Issues, PC and Laptop Issues…) or type a new one.' },
    { el: '#wz-audience', step: 1, title: 'Who can open it', text: 'Everyone, only some roles, or IT and Admin only (setup guides that are never sent to requesters).' },
    { el: '#wz-problem', step: 2, title: '2. The problem', text: 'Introduction, Main Issue (what the user sees) and Factors (possible causes, one per line). Each box is one slide; empty boxes are skipped.' },
    { el: '#wz-steps', step: 3, title: '3. The fix: steps', text: 'Click "Add a step" for each action and add a photo where it helps. Use the arrows to change the order. Section titles start the numbers again.' },
    { el: '#wz-preview', title: 'Live preview', text: 'Your slides appear here as you type. Click a slide to jump to the box it comes from.' },
    { el: '#wz-match', step: 4, title: '4. When is it sent?', text: 'Tick the ticket categories and add keywords (or press Suggest). When a ticket matches, the guide is sent in its chat.' },
    { el: '#wz-save', step: 4, title: 'Save', text: 'Save the guide. It is in the manuals library right away, with its slides and PowerPoint.' },
    { el: '#tabList', title: 'All guides', text: 'Here you find every guide: View slides, PowerPoint, Edit (pencil) or Delete (trash).' },
    { el: '#tabTest', title: 'Test', text: 'Type a ticket like a user would, and see which guide the TMS sends.' },
  ];
  var TOUR_KEY = 'tms_manual_tour_seen';
  var ring = null, tip = null, tourIndex = 0;
  function stopTour() {
    if (ring) ring.remove();
    if (tip) tip.remove();
    ring = tip = null;
    window.removeEventListener('resize', place);
    try { localStorage.setItem(TOUR_KEY, '1'); } catch (e) {}
  }
  function place() {
    var target = document.querySelector(TOUR[tourIndex].el);
    if (!target || !ring) return;
    var r = target.getBoundingClientRect(), sx = window.pageXOffset, sy = window.pageYOffset;
    ring.style.left = (r.left + sx - 6) + 'px';
    ring.style.top = (r.top + sy - 6) + 'px';
    ring.style.width = (r.width + 12) + 'px';
    ring.style.height = Math.min(r.height + 12, 520) + 'px';
    var tipTop = r.bottom + sy + 14;
    if (r.bottom + tip.offsetHeight + 30 > window.innerHeight && r.top > tip.offsetHeight + 30) tipTop = r.top + sy - tip.offsetHeight - 14;
    tip.style.top = tipTop + 'px';
    tip.style.left = Math.max(12, Math.min(r.left + sx, sx + document.documentElement.clientWidth - tip.offsetWidth - 12)) + 'px';
  }
  function showTour(i) {
    tourIndex = i;
    var s = TOUR[i];
    if (s.el !== '#tabList' && s.el !== '#tabTest') openTab('create');
    if (s.step) go(s.step);
    var target = document.querySelector(s.el);
    if (!target) { if (i < TOUR.length - 1) showTour(i + 1); else stopTour(); return; }
    if (!ring) {
      ring = el('div', 'tour-ring'); document.body.appendChild(ring);
      tip = el('div', 'tour-tip'); tip.setAttribute('role', 'dialog'); document.body.appendChild(tip);
      window.addEventListener('resize', place);
    }
    tip.textContent = '';
    tip.appendChild(el('h6', null, s.title));
    tip.appendChild(el('p', null, s.text));
    var bar = el('div', 'd-flex align-items-center');
    bar.appendChild(el('span', 'tour-n mr-auto', (i + 1) + ' of ' + TOUR.length));
    var close = el('button', 'btn btn-sm btn-light mr-1', 'Close'); close.type = 'button';
    var prev = el('button', 'btn btn-sm btn-outline-primary mr-1', 'Back'); prev.type = 'button';
    var nxt = el('button', 'btn btn-sm btn-primary', i === TOUR.length - 1 ? 'Done' : 'Next'); nxt.type = 'button';
    prev.disabled = i === 0;
    close.onclick = stopTour;
    prev.onclick = function () { showTour(i - 1); };
    nxt.onclick = function () { if (i === TOUR.length - 1) { stopTour(); openTab('create'); go(1); } else showTour(i + 1); };
    bar.appendChild(close); bar.appendChild(prev); bar.appendChild(nxt);
    tip.appendChild(bar);
    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(place, 420);
    nxt.focus({ preventScroll: true });
  }
  $('tourBtn').addEventListener('click', function () { showTour(0); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && ring) stopTour(); });

  // "Create a guide" in the header opens the tab without leaving the page.
  var createBtn = $('createBtn');
  if (createBtn && cfg.isNew) {
    createBtn.addEventListener('click', function (e) { e.preventDefault(); openTab('create'); go(1); $('m_title').focus(); });
  }

  // ---------------------------------------------------------------- start
  render();
  go(1);
  renderPreview();
  updateSentence();
  // First visit to the guide maker: show how it works once.
  var seen = false;
  try { seen = localStorage.getItem(TOUR_KEY) === '1'; } catch (e) {}
  var createOpen = !document.querySelector('.me-pane[data-pane="create"]').hidden;
  if (!seen && createOpen && cfg.isNew) afterPopups(function () { showTour(1); });

  // Wait until the sign-in popups (announcements, birthdays) are closed.
  function afterPopups(fn) {
    var started = false;
    // Then also wait for the birthday greeting and the first-time guide to be closed.
    var run = function () {
      if (started) return;
      if (document.querySelector('.tmsw-modal, .tmsw-bday')) { setTimeout(run, 800); return; }
      started = true;
      setTimeout(fn, 400);
    };
    // The birthday greeting and the guide open a moment after the announcements close.
    var later = function () { setTimeout(run, 1200); };
    if (window.__tmsAnnDone) { later(); return; }
    document.addEventListener('tms:announcements-done', later);
    setTimeout(function () { if (!document.querySelector('.tmsw-ann-modal')) run(); }, 15000);
  }
})();
