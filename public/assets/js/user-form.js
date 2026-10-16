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
      });
    }
  });
})();
