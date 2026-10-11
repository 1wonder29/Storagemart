/*
 * Writing helper for the ticket form (every role): while the person types the Subject and the
 * Description it shows what is still missing (which device, where, what happens) and suggestions
 * to click, so a ticket becomes "Brother DCP-L2540DW printer at the front desk shows Paper Jam"
 * instead of "Printer is not working". Clicking a suggestion only adds text; nothing is sent.
 */
(function () {
  'use strict';
  var subject = document.getElementById('subject');
  var description = document.getElementById('concern_details');
  var category = document.getElementById('category');
  if (!subject || !description || subject.__tmsWriter) return;
  subject.__tmsWriter = true;

  var brands = /\b(epson|brother|canon|hp|lenovo|asus|acer|dell|msi|samsung|xiaomi|huawei|tp-?link|cisco|mikrotik|ubiquiti|hikvision|dahua|kedacom|ezviz|zkteco|panasonic|logitech|a4tech|apc|secure|realme|oppo|vivo|iphone|apple|toshiba|sharp|ricoh|xerox|kyocera|pldt|globe|converge|smart|chuwi|dere|nvision|viewsonic|aoc|philips|grandstream|yealink|ingenico|verifone|sunmi|fortinet|tenda|d-?link|ruijie|storlogix|pti|outlook|gmail|viber|chrome|excel)\b/i;
  var modelToken = /\b(?!\d+(?:st|nd|rd|th)\b)(?=[a-z0-9-]*\d)(?=[a-z0-9-]*[a-z])[a-z0-9-]{3,}\b/i;
  var unitToken = /\b(camera|cam|channel|ch|unit|terminal|pc|computer|station|printer|phone|line|port|biometric|no)\.?\s*#?\s*\d+\b/i;
  var placeRe = /\b(front ?desk|counter|reception|lobby|office|room|floor|area|desk|station|cashier|admin|warehouse|unit|aisle|entrance|gate|door|stock ?room|pantry|conference|meeting|branch|bodega|taas|baba|ground|upstairs|downstairs|hallway|guard|server|rack|cabinet|table|head office)\b/i;
  var symptomRe = /\b(not|no|won'?t|cannot|can'?t|unable|error|jam|jammed|offline|slow|freez|hang|stuck|blank|black|blue screen|bsod|restart|shut|broken|crack|faded|streak|beep|noise|flicker|blurry|dead|lost|missing|full|wrong|keeps|loop|alarm|disconnect|drops|red light|los|blinking|ayaw|sira|hindi|walang|mabagal)\w*/i;

  // Device families: how to recognise them, an example subject, and endings to suggest.
  var DEVICES = [
    { key: 'printer', models: ['Brother DCP-L2540DW', 'Brother MFC-T810W', 'Epson L3210', 'Canon G2010'],
      re: /\b(printer|print|brother|epson|canon|toner|drum|scan|scanner|paper)\b/i, label: 'Brother DCP-L2540DW printer',
      example: 'Brother DCP-L2540DW printer at the front desk shows Paper Jam',
      endings: ['shows Paper Jam', 'is offline and will not print', 'prints faded or streaky pages', 'shows "Replace Toner"', 'cannot scan to the PC'] },
    { key: 'pc', models: ['Lenovo', 'Acer', 'Asus', 'Dell', 'PC #1'],
      re: /\b(pc|computer|desktop|laptop|monitor|keyboard|mouse|windows|lenovo|acer|asus|dell)\b/i, label: 'Lenovo desktop PC',
      example: 'Lenovo desktop PC at the cashier will not turn on since this morning',
      endings: ['will not turn on', 'is very slow', 'freezes / not responding', 'shows a blue screen (BSOD)', 'keeps restarting'] },
    { key: 'internet', models: ['PLDT', 'Converge', 'Globe', 'TP-Link'],
      re: /\b(internet|wifi|wi-fi|network|pldt|globe|converge|router|modem|los|connection|lan)\b/i, label: 'PLDT internet',
      example: 'PLDT internet at the branch has no connection, red LOS light is blinking',
      endings: ['has no connection (red LOS light)', 'is very slow', 'keeps disconnecting', 'Wi-Fi does not show up'] },
    { key: 'cctv', models: ['camera 1', 'camera 2', 'Hikvision NVR', 'Dahua NVR'],
      re: /\b(cctv|camera|nvr|vstation|dvr|footage)\b/i, label: 'CCTV camera',
      example: 'CCTV camera 3 at the entrance is offline on the monitor',
      endings: ['is offline on the monitor', 'monitor has no display', 'cannot play back footage', 'NVR cannot connect'] },
    { key: 'email', models: ['Outlook', 'Gmail'],
      re: /\b(email|e-mail|outlook|gmail|mail|inbox|attachment)\b/i, label: 'Outlook email',
      example: 'Outlook on the admin office PC cannot send emails since 9 AM',
      endings: ['cannot send emails', 'cannot receive emails', 'keeps asking for the password', 'attachment is too large'] },
    { key: 'phone', models: ['Samsung', 'Xiaomi', 'Realme', 'phone #1'],
      re: /\b(phone|cellphone|mobile|telephone|landline|viber|sim)\b/i, label: 'company phone',
      example: 'Company phone of the branch facilitator keeps restarting by itself',
      endings: ['keeps restarting by itself', 'has no sound', 'storage is full', 'has no dial tone'] },
    { key: 'pti', models: ['PTI Storlogix', 'gate keypad #1', 'ZKTeco biometric'],
      re: /\b(pti|storlogix|alarm|gate|keypad|access control|biometric)\b/i, label: 'PTI Storlogix',
      example: 'PTI Storlogix at the front desk is offline and gate access does not work',
      endings: ['is offline', 'unit alarm goes off by itself', 'gate keypad does not respond', 'biometric does not read fingers'] },
    { key: 'pos', models: ['Ingenico', 'Verifone', 'Sunmi', 'terminal 1'],
      re: /\b(pos|terminal|payment|card reader|ingenico|verifone|sunmi)\b/i, label: 'POS / payment terminal',
      example: 'Payment terminal at the counter declines all cards since 10 AM',
      endings: ['declines all cards', 'will not turn on', 'cannot print receipts', 'shows a connection error'] }
  ];
  var PLACES = ['at the front desk', 'at the counter', 'in the admin office', 'at the cashier', 'at the entrance', 'on the 2nd floor'];
  var DESC_PROMPTS = [
    ['Since when?', 'It started this morning around 9 AM.'],
    ['Error message', 'The screen shows this message: "..."'],
    ['What I tried', 'I already restarted it, but the problem is still there.'],
    ['Who is affected', 'Only this device is affected; the other ones work.'],
    ['How urgent', 'We cannot serve customers until it is fixed.']
  ];

  var css = document.createElement('style');
  css.textContent = [
    '.tw-box{margin-top:8px;padding:10px 12px;border-radius:12px;background:#f5f8fe;border:1px solid #e1e8f6;font-size:.82rem}',
    '.tw-checks{display:flex;flex-wrap:wrap;gap:6px 14px;margin-bottom:6px}',
    '.tw-check{color:#94a3b8;font-weight:700}.tw-check.ok{color:#0e9f6e}',
    '.tw-check i{margin-right:4px}',
    '.tw-row{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:6px}',
    '.tw-row>span{font-weight:700;color:#475569;margin-right:2px}',
    '.tw-chip{border:1px solid #c9d5ee;background:#fff;color:#19398a;border-radius:999px;padding:2px 10px;font-size:.78rem;font-weight:600;cursor:pointer;transition:all .12s}',
    '.tw-chip:hover,.tw-chip:focus{background:#19398a;color:#fff;border-color:#19398a;outline:none}',
    '.tw-chip.tw-ex{border-style:dashed;color:#c2410c;border-color:#f7b48a}',
    '.tw-chip.tw-ex:hover{background:#f85f00;border-color:#f85f00;color:#fff}',
    '.tw-meter{display:flex;align-items:center;gap:8px;margin-bottom:4px;font-weight:700}',
    '.tw-bar{flex:0 0 140px;height:6px;border-radius:99px;background:#e2e8f0;overflow:hidden}',
    '.tw-bar i{display:block;height:100%;width:0;border-radius:99px;background:#ef4444;transition:width .2s,background .2s}',
    '.tw-good{color:#0e9f6e}.tw-mid{color:#d97706}.tw-low{color:#dc2626}',
    'html.it-dark .tw-box{background:#1f2433;border-color:#343a4f}',
    'html.it-dark .tw-chip{background:#2a3042;color:#cbd5f5;border-color:#3a4258}',
    'html.it-dark .tw-row>span{color:#cbd5e1}'
  ].join('');
  document.head.appendChild(css);

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  }
  function chip(text, title, onClick, extra) {
    var b = el('button', 'tw-chip' + (extra ? ' ' + extra : ''), text);
    b.type = 'button';
    if (title) b.title = title;
    b.addEventListener('mousedown', function (e) { e.preventDefault(); }); // keep the cursor in the field
    b.addEventListener('click', onClick);
    return b;
  }
  function addText(field, text, asSentence) {
    var value = field.value.replace(/\s+$/, '');
    if (asSentence) {
      if (value && !/[.!?]$/.test(value)) value += '.';
      field.value = (value ? value + ' ' : '') + text;
    } else {
      field.value = (value ? value + ' ' : '') + text;
    }
    field.dispatchEvent(new Event('input', { bubbles: true }));
    field.focus();
    try { field.setSelectionRange(field.value.length, field.value.length); } catch (e) { /* not a text field */ }
  }
  function detectDevice(text) {
    for (var i = 0; i < DEVICES.length; i++) {
      if (DEVICES[i].re.test(text)) return DEVICES[i];
    }
    return null;
  }

  /* ---------- Subject helper ---------- */
  var sBox = el('div', 'tw-box');
  sBox.setAttribute('aria-live', 'polite');
  var sHelp = subject.parentNode.querySelector('.form-text');
  (sHelp || subject).insertAdjacentElement('afterend', sBox);

  function renderSubject() {
    var text = subject.value.trim();
    var all = text + ' ' + description.value + ' ' + (category ? category.value : '');
    var device = detectDevice(text) || (text ? null : detectDevice(category ? category.value : ''));
    var family = detectDevice(text);
    var hasModel = brands.test(text) || modelToken.test(text) || unitToken.test(text);
    var hasDevice = hasModel; // a device word alone ("printer") is not enough: which one?
    var hasPlace = placeRe.test(text);
    var hasSymptom = symptomRe.test(text);
    sBox.innerHTML = '';
    var checks = el('div', 'tw-checks');
    [['Which device (brand / model)', hasDevice], ['Where', hasPlace], ['What happens', hasSymptom]].forEach(function (c) {
      var item = el('span', 'tw-check' + (c[1] ? ' ok' : ''));
      var i = el('i', c[1] ? 'fas fa-check-circle' : 'far fa-circle');
      item.appendChild(i);
      item.appendChild(document.createTextNode(c[0]));
      checks.appendChild(item);
    });
    sBox.appendChild(checks);

    if (hasDevice && hasPlace && hasSymptom) {
      var good = el('div', 'tw-good', '✓ Good subject. IT can see what and where at a glance.');
      sBox.appendChild(good);
      return;
    }
    var row = el('div', 'tw-row');
    if (!hasDevice && family) {
      row.appendChild(el('span', null, 'Which one? Add the brand / model:'));
      family.models.forEach(function (m) {
        row.appendChild(chip(m, null, function () {
          // "printer is not working" -> "Brother DCP-L2540DW printer is not working"
          var match = family.re.exec(subject.value);
          if (match) {
            subject.value = subject.value.slice(0, match.index) + m + ' ' + subject.value.slice(match.index);
            subject.dispatchEvent(new Event('input', { bubbles: true }));
            subject.focus();
          } else {
            addText(subject, m);
          }
        }));
      });
    } else if (!hasDevice) {
      row.appendChild(el('span', null, 'Add the device:'));
      DEVICES.slice(0, 6).forEach(function (d) {
        row.appendChild(chip(d.label, 'Add "' + d.label + '"', function () { addText(subject, d.label); }));
      });
    } else if (!hasPlace) {
      row.appendChild(el('span', null, 'Add where:'));
      PLACES.forEach(function (p) { row.appendChild(chip(p, null, function () { addText(subject, p); })); });
    } else {
      row.appendChild(el('span', null, 'Add what happens:'));
      (device || DEVICES[0]).endings.forEach(function (end) {
        row.appendChild(chip(end, null, function () { addText(subject, end); }));
      });
    }
    sBox.appendChild(row);
    var example = device || detectDevice(all);
    if (example) {
      var ex = el('div', 'tw-row');
      ex.appendChild(el('span', null, 'Example:'));
      ex.appendChild(chip(example.example, 'Use this example (replaces the subject); then change the details', function () {
        subject.value = example.example;
        subject.dispatchEvent(new Event('input', { bubbles: true }));
        subject.focus();
      }, 'tw-ex'));
      sBox.appendChild(ex);
    }
  }

  /* ---------- Description helper ---------- */
  var dBox = el('div', 'tw-box');
  dBox.setAttribute('aria-live', 'polite');
  var dHelp = description.parentNode.querySelector('.form-text');
  (dHelp || description).insertAdjacentElement('afterend', dBox);
  var meter = el('div', 'tw-meter');
  var bar = el('span', 'tw-bar');
  var fill = el('i');
  bar.appendChild(fill);
  var meterText = el('span');
  meter.appendChild(bar);
  meter.appendChild(meterText);
  dBox.appendChild(meter);
  var prompts = el('div', 'tw-row');
  prompts.appendChild(el('span', null, 'Add a sentence:'));
  DESC_PROMPTS.forEach(function (p) {
    prompts.appendChild(chip(p[0], p[1], function () { addText(description, p[1], true); }));
  });
  dBox.appendChild(prompts);

  function renderDescription() {
    var words = (description.value.trim().match(/[\p{L}\p{N}][\p{L}\p{N}'-]*/gu) || []).length;
    var pct = Math.min(100, Math.round(words / 25 * 100));
    fill.style.width = pct + '%';
    var cls = words >= 15 ? 'tw-good' : (words >= 6 ? 'tw-mid' : 'tw-low');
    fill.style.background = words >= 15 ? '#0e9f6e' : (words >= 6 ? '#f59e0b' : '#ef4444');
    meterText.className = cls;
    meterText.textContent = words === 0 ? 'Describe what happens (at least one full sentence)'
      : words < 6 ? words + ' words · too short, add more detail'
      : words < 15 ? words + ' words · OK, a little more helps IT'
      : words + ' words · great detail';
  }

  subject.addEventListener('input', renderSubject);
  description.addEventListener('input', function () { renderDescription(); renderSubject(); });
  if (category) category.addEventListener('change', renderSubject);
  renderSubject();
  renderDescription();
})();
