/*
 * Add User (Admin), Edit User and New user request (HR):
 *  - data-numbers-only: Employee ID takes digits only; data-numbers-only="phone" also allows one + at the start.
 *    Anything else typed or pasted is removed, and a short note says why.
 *  - data-company-email: suggests storagemart.<first name><last name>@gmail.com from the name fields
 *    while the email is empty or still the earlier suggestion (it can always be changed).
 *  - data-gmail-formula: the Gmail password follows the corporate list formula, middle name + Employee ID
 *    (Excel =CONCATENATE(middle name, FORMAT)), until someone types another one.
 */
(function () {
  'use strict';

  function note(input, text) {
    var tip = input.parentNode.querySelector('.numbers-only-note');
    if (!tip) {
      tip = document.createElement('small');
      tip.className = 'form-text text-warning numbers-only-note';
      tip.setAttribute('aria-live', 'polite');
      (input.closest('.input-group') || input).insertAdjacentElement('afterend', tip);
    }
    tip.textContent = text;
    clearTimeout(tip._t);
    tip._t = setTimeout(function () { tip.textContent = ''; }, 2500);
  }

  document.querySelectorAll('[data-numbers-only]').forEach(function (input) {
    var phone = input.getAttribute('data-numbers-only') === 'phone';
    input.setAttribute('inputmode', phone ? 'tel' : 'numeric');
    input.addEventListener('input', function () {
      var v = input.value;
      var clean = phone ? v.replace(/(?!^\+)[^0-9]/g, '') : v.replace(/[^0-9]/g, '');
      if (clean !== v) {
        var pos = input.selectionStart - (v.length - clean.length);
        input.value = clean;
        try { input.setSelectionRange(Math.max(0, pos), Math.max(0, pos)); } catch (e) {}
        note(input, phone ? 'Numbers only (for example 09171234567).' : 'Numbers only.');
      }
    });
  });

  function slug(s) {
    s = String(s || '').toLowerCase();
    if (s.normalize) s = s.normalize('NFD').replace(/[̀-ͯ]/g, '');
    return s.replace(/ñ/g, 'n').replace(/[^a-z]/g, '');
  }

  var email = document.querySelector('[data-company-email]');
  if (email) {
    var first = document.querySelector(email.getAttribute('data-first') || '#firstname');
    var last = document.querySelector(email.getAttribute('data-last') || '#lastname');
    var hint = document.createElement('small');
    hint.className = 'form-text text-muted company-email-hint';
    email.insertAdjacentElement('afterend', hint);
    var lastSuggestion = '';

    var suggest = function () {
      var local = slug(first && first.value) + slug(last && last.value);
      var s = local ? 'storagemart.' + local + '@gmail.com' : '';
      var current = email.value.trim();
      if (s && (current === '' || current === lastSuggestion)) {
        email.value = s;
        current = s;
      }
      lastSuggestion = s;
      hint.textContent = '';
      if (s && current.toLowerCase() !== s) {
        hint.appendChild(document.createTextNode('Company Gmail format: '));
        var use = document.createElement('a');
        use.href = '#';
        use.textContent = s;
        use.addEventListener('click', function (e) {
          e.preventDefault();
          email.value = s;
          suggest();
        });
        hint.appendChild(use);
      } else if (s) {
        hint.textContent = 'Company Gmail format: storagemart. + first name + last name @gmail.com';
      }
      document.dispatchEvent(new Event('tms-sync'));
    };
    if (first) first.addEventListener('input', suggest);
    if (last) last.addEventListener('input', suggest);
    email.addEventListener('input', suggest);
    // On a form sent back with an error the typed address stays as it is.
    lastSuggestion = email.value.trim() === '' ? '' : null;
    suggest();
  }

  document.querySelectorAll('[data-gmail-formula]').forEach(function (input) {
    var middle = document.querySelector('#middlename');
    var last = document.querySelector('#lastname');
    var id = document.querySelector('#employee_id');
    var formula = function () {
      var name = (middle && middle.value.trim()) || (last && last.value.trim()) || '';
      var num = id ? id.value.trim() : '';
      return name && num ? name + num : '';
    };
    // An address typed before an error stays; otherwise the field follows the formula.
    var lastAuto = input.value === '' ? '' : null;
    var update = function () {
      var f = formula();
      if (input.value === '' || input.value === lastAuto) input.value = f;
      lastAuto = f;
      document.dispatchEvent(new Event('tms-sync'));
    };
    [middle, last, id].forEach(function (el) { if (el) el.addEventListener('input', update); });
    update();
    var btn = document.querySelector('[data-gmail-formula-btn="#' + input.id + '"]');
    if (btn) {
      btn.addEventListener('click', function () {
        input.value = formula();
        lastAuto = input.value;
        input.focus();
        input.select();
        document.dispatchEvent(new Event('tms-sync'));
      });
    }
  });

  // Username follows the Gmail address (the TMS login is the company Gmail), until typed by hand.
  document.querySelectorAll('[data-follow-email]').forEach(function (user) {
    var source = document.querySelector(user.getAttribute('data-follow-email'));
    if (!source) return;
    var lastAuto = user.value === '' ? '' : null;
    var follow = function () {
      var v = source.value.trim();
      if (user.value === '' || user.value === lastAuto) user.value = v;
      lastAuto = v;
    };
    source.addEventListener('input', follow);
    document.addEventListener('tms-sync', follow);
    follow();
  });

  // Some roles only work in one department (IT staff must be in IT to be assigned tickets).
  document.querySelectorAll('[data-role-department]').forEach(function (role) {
    var map = {};
    try { map = JSON.parse(role.getAttribute('data-role-department') || '{}'); } catch (e) {}
    var dept = document.querySelector('#department');
    if (!dept) return;
    var tip = document.createElement('small');
    tip.className = 'form-text text-info role-department-note';
    dept.insertAdjacentElement('afterend', tip);
    var apply = function (fromRole) {
      var need = map[role.value];
      tip.textContent = need ? role.options[role.selectedIndex].text + ' accounts are always in this department.' : '';
      if (need && fromRole) dept.value = need;
      dept.setCustomValidity(need && dept.value !== need ? 'Choose the ' + need + ' department for this user type.' : '');
    };
    role.addEventListener('change', function () { apply(true); });
    dept.addEventListener('change', function () { apply(false); });
    apply(false);
  });

  // Company Gmail step: QR code, Google sign-up beside the page, live values, copy, progress.
  document.querySelectorAll('[data-gmail-setup]').forEach(function (box) {
    var qrBox = box.querySelector('[data-signup-qr]');
    if (qrBox) {
      if (window.qrcode) {
        var qr = window.qrcode(0, 'M');
        qr.addData(qrBox.getAttribute('data-signup-qr'));
        qr.make();
        qrBox.innerHTML = qr.createSvgTag(5, 0);
      } else {
        qrBox.textContent = 'QR code not available. Use the button below.';
      }
    }
    var openBtn = box.querySelector('[data-open-signup]');
    if (openBtn) {
      openBtn.addEventListener('click', function () {
        var w = Math.min(520, screen.availWidth);
        var h = Math.min(820, screen.availHeight - 40);
        var left = (screen.availLeft || 0) + screen.availWidth - w - 10;
        var top = (screen.availTop || 0) + 20;
        var win = window.open(openBtn.getAttribute('data-open-signup'), 'tmsGoogleSignup',
          'width=' + w + ',height=' + h + ',left=' + left + ',top=' + top + ',resizable=yes,scrollbars=yes');
        if (win) { win.focus(); } else { window.open(openBtn.getAttribute('data-open-signup'), '_blank', 'noopener'); }
      });
    }
    var email = document.querySelector('#email');
    var pw = document.querySelector('#gmail_password');
    var localEl = box.querySelector('[data-gs-local]');
    var pwEl = box.querySelector('[data-gs-password]');
    var values = { local: '', password: '' };
    var refresh = function () {
      var e = email ? email.value.trim() : '';
      values.local = e.indexOf('@') > 0 ? e.slice(0, e.indexOf('@')) : e;
      values.password = pw ? pw.value : '';
      if (localEl) localEl.textContent = values.local || 'storagemart.firstnamelastname';
      if (pwEl) pwEl.textContent = values.password || 'middle name + Employee ID';
    };
    [email, pw].forEach(function (el) { if (el) el.addEventListener('input', refresh); });
    document.addEventListener('tms-sync', refresh);
    refresh();
    box.querySelectorAll('[data-gs-copy]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var text = values[btn.getAttribute('data-gs-copy')] || '';
        if (!text) return;
        var done = function () {
          var old = btn.innerHTML;
          btn.innerHTML = '<i class="fas fa-check"></i>';
          setTimeout(function () { btn.innerHTML = old; }, 1200);
        };
        if (navigator.clipboard && window.isSecureContext) {
          navigator.clipboard.writeText(text).then(done, done);
        } else {
          var t = document.createElement('textarea');
          t.value = text;
          document.body.appendChild(t);
          t.select();
          try { document.execCommand('copy'); } catch (e) {}
          t.remove();
          done();
        }
      });
    });
    var checks = box.querySelectorAll('input[type="checkbox"]');
    var progress = box.querySelector('[data-gs-progress]');
    var count = function () {
      var n = 0;
      checks.forEach(function (c) {
        c.closest('.gs-check').classList.toggle('is-done', c.checked);
        if (c.checked) n++;
      });
      if (progress) {
        progress.textContent = n === checks.length
          ? 'All ' + n + ' steps done. You can create the account.'
          : n + ' of ' + checks.length + ' steps done. Tick all of them to create the account.';
        progress.classList.toggle('is-done', n === checks.length);
      }
    };
    checks.forEach(function (c) { c.addEventListener('change', count); });
    count();
  });
})();
