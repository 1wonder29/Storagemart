/*
 * TMS confirm and message windows.
 *
 * Replaces the browser's own confirm() / alert() pop-ups. Some browsers block those
 * (built-in app browsers, or Chrome after "Prevent this page from creating additional
 * dialogs"): a blocked confirm() counts as "Cancel", so the button seemed to do nothing.
 *
 *   <form data-confirm="Delete this item?">           asks before the form is sent
 *   <a href="..." data-confirm="Delete this branch?">  asks before the link is opened
 *   <button type="submit" data-confirm="...">          asks before that button submits
 *   TmsDialog.confirm('Text').then(function (ok) { ... });
 *   TmsDialog.alert('Text');                           (window.alert uses this too)
 *
 * Optional attributes: data-confirm-ok (button text), data-confirm-title, data-confirm-danger.
 */
(function () {
  'use strict';
  if (window.TmsDialog) return;

  var CSS = '' +
    '.tmsd-backdrop{position:fixed;inset:0;z-index:20000;display:flex;align-items:center;justify-content:center;padding:16px;' +
    'background:rgba(15,23,42,.55);backdrop-filter:blur(2px);animation:tmsd-fade .15s ease-out}' +
    '.tmsd-box{width:100%;max-width:420px;background:#fff;color:#1f2937;border-radius:16px;box-shadow:0 24px 60px rgba(15,23,42,.35);' +
    'padding:22px 22px 18px;font-family:inherit;animation:tmsd-pop .18s ease-out}' +
    '.tmsd-head{display:flex;align-items:center;gap:12px;margin-bottom:10px}' +
    '.tmsd-icon{flex:0 0 42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;' +
    'background:rgba(38,97,212,.12);color:#2661D4}' +
    '.tmsd-box.is-danger .tmsd-icon{background:rgba(220,38,38,.12);color:#dc2626}' +
    '.tmsd-box.is-info .tmsd-icon{background:rgba(248,95,0,.12);color:#F85F00}' +
    '.tmsd-title{font-size:1.05rem;font-weight:700;margin:0;color:#0F2563}' +
    '.tmsd-msg{margin:0 0 18px;font-size:.95rem;line-height:1.5;white-space:pre-line;word-break:break-word}' +
    '.tmsd-actions{display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap}' +
    '.tmsd-btn{border:0;border-radius:10px;padding:.5rem 1.05rem;font-weight:600;font-size:.9rem;cursor:pointer;line-height:1.3}' +
    '.tmsd-btn:focus-visible{outline:3px solid rgba(38,97,212,.45);outline-offset:2px}' +
    '.tmsd-cancel{background:#eef1f6;color:#374151}.tmsd-cancel:hover{background:#e2e7ef}' +
    '.tmsd-ok{background:linear-gradient(135deg,#2661D4,#19398A);color:#fff}.tmsd-ok:hover{filter:brightness(1.08)}' +
    '.tmsd-box.is-danger .tmsd-ok{background:linear-gradient(135deg,#ef4444,#b91c1c)}' +
    'html.it-dark .tmsd-box{background:#252932;color:#e2e5ea;box-shadow:0 24px 60px rgba(0,0,0,.6)}' +
    'html.it-dark .tmsd-title{color:#fff}' +
    'html.it-dark .tmsd-cancel{background:#343a46;color:#e2e5ea}html.it-dark .tmsd-cancel:hover{background:#3d4452}' +
    'html.it-dark .tmsd-icon{background:rgba(96,140,240,.18);color:#8fb0ff}' +
    'html.it-dark .tmsd-box.is-danger .tmsd-icon{background:rgba(248,113,113,.18);color:#fca5a5}' +
    '@keyframes tmsd-fade{from{opacity:0}to{opacity:1}}' +
    '@keyframes tmsd-pop{from{opacity:0;transform:translateY(8px) scale(.97)}to{opacity:1;transform:none}}' +
    '@media (prefers-reduced-motion:reduce){.tmsd-backdrop,.tmsd-box{animation:none}}';

  var DANGER_WORDS = /\b(delete|remove|reject|deactivate|clear|discard|end this|take back|cancel this|permanently)\b/i;
  var queue = Promise.resolve();

  function addStyles() {
    if (document.getElementById('tms-dialog-css')) return;
    var style = document.createElement('style');
    style.id = 'tms-dialog-css';
    style.textContent = CSS;
    (document.head || document.documentElement).appendChild(style);
  }

  function iconHtml(kind) {
    var fa = kind === 'danger' ? 'fa-exclamation-triangle' : (kind === 'info' ? 'fa-info' : 'fa-question');
    return '<i class="fas ' + fa + '" aria-hidden="true"></i>';
  }

  /** Shows one window and resolves true (OK) or false (Cancel / Esc / click outside). */
  function show(opts) {
    addStyles();
    return new Promise(function (resolve) {
      var lastFocus = document.activeElement;
      var backdrop = document.createElement('div');
      backdrop.className = 'tmsd-backdrop';
      var box = document.createElement('div');
      box.className = 'tmsd-box' + (opts.kind === 'danger' ? ' is-danger' : (opts.kind === 'info' ? ' is-info' : ''));
      box.setAttribute('role', opts.alert ? 'alertdialog' : 'dialog');
      box.setAttribute('aria-modal', 'true');
      var id = 'tmsd' + Date.now();
      box.setAttribute('aria-labelledby', id + 't');
      box.setAttribute('aria-describedby', id + 'm');

      var head = document.createElement('div');
      head.className = 'tmsd-head';
      head.innerHTML = '<span class="tmsd-icon">' + iconHtml(opts.kind) + '</span>';
      var title = document.createElement('h2');
      title.className = 'tmsd-title';
      title.id = id + 't';
      title.textContent = opts.title;
      head.appendChild(title);

      var msg = document.createElement('p');
      msg.className = 'tmsd-msg';
      msg.id = id + 'm';
      msg.textContent = opts.message;

      var actions = document.createElement('div');
      actions.className = 'tmsd-actions';
      var cancel = null;
      if (!opts.alert) {
        cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'tmsd-btn tmsd-cancel';
        cancel.textContent = opts.cancelText || 'Cancel';
        actions.appendChild(cancel);
      }
      var ok = document.createElement('button');
      ok.type = 'button';
      ok.className = 'tmsd-btn tmsd-ok';
      ok.textContent = opts.okText || 'OK';
      actions.appendChild(ok);

      box.appendChild(head);
      box.appendChild(msg);
      box.appendChild(actions);
      backdrop.appendChild(box);
      document.body.appendChild(backdrop);

      var done = false;
      function close(result) {
        if (done) return;
        done = true;
        document.removeEventListener('keydown', onKey, true);
        backdrop.remove();
        if (lastFocus && lastFocus.focus) {
          try { lastFocus.focus({ preventScroll: true }); } catch (e) {}
        }
        resolve(result);
      }
      function onKey(e) {
        if (e.key === 'Escape') {
          e.preventDefault();
          e.stopPropagation();
          close(!!opts.alert);
        } else if (e.key === 'Tab') {
          // Keep the keyboard inside the window.
          var items = cancel ? [cancel, ok] : [ok];
          var i = items.indexOf(document.activeElement);
          e.preventDefault();
          items[(i + (e.shiftKey ? items.length - 1 : 1)) % items.length].focus();
        }
      }
      ok.addEventListener('click', function () { close(true); });
      if (cancel) cancel.addEventListener('click', function () { close(false); });
      backdrop.addEventListener('mousedown', function (e) {
        if (e.target === backdrop) close(!!opts.alert);
      });
      document.addEventListener('keydown', onKey, true);
      // Dangerous actions start on Cancel so a stray Enter does not delete anything.
      ((opts.kind === 'danger' && cancel) ? cancel : ok).focus();
    });
  }

  function enqueue(opts) {
    var run = queue.then(function () { return show(opts); });
    queue = run.catch(function () {});
    return run;
  }

  function kindFor(message, el) {
    if (el && el.hasAttribute && el.hasAttribute('data-confirm-danger')) return 'danger';
    if (el && el.classList && (el.classList.contains('btn-danger') || el.classList.contains('btn-outline-danger'))) return 'danger';
    return DANGER_WORDS.test(String(message || '')) ? 'danger' : 'question';
  }

  function cleanLabel(el) {
    if (!el) return '';
    var text = (el.getAttribute('data-confirm-ok') || el.textContent || el.value || '').replace(/\s+/g, ' ').trim();
    return text.length > 0 && text.length <= 28 ? text : '';
  }

  var TmsDialog = {
    /** Resolves true when the person clicks OK. opts: {title, okText, cancelText, danger} */
    confirm: function (message, opts) {
      opts = opts || {};
      return enqueue({
        message: String(message == null ? '' : message),
        title: opts.title || 'Please confirm',
        okText: opts.okText || 'Yes, continue',
        cancelText: opts.cancelText,
        kind: opts.danger === true ? 'danger' : (opts.danger === false ? 'question' : kindFor(message, opts.element))
      });
    },
    /** Shows a message with one OK button. Resolves when it is closed. */
    alert: function (message, opts) {
      opts = opts || {};
      return enqueue({
        message: String(message == null ? '' : message),
        title: opts.title || 'Notice',
        okText: opts.okText || 'OK',
        alert: true,
        kind: 'info'
      });
    }
  };
  window.TmsDialog = TmsDialog;

  // alert() never waits for an answer, so the page keeps working the same way.
  window.alert = function (message) { TmsDialog.alert(message); };

  function confirmFor(el, fallbackEl) {
    var message = el.getAttribute('data-confirm');
    var labelEl = fallbackEl || el;
    return TmsDialog.confirm(message, {
      title: el.getAttribute('data-confirm-title') || undefined,
      okText: cleanLabel(el.hasAttribute('data-confirm-ok') ? el : labelEl) || undefined,
      element: el.hasAttribute('data-confirm-danger') ? el : labelEl
    });
  }

  // Links and buttons that carry data-confirm themselves.
  document.addEventListener('click', function (e) {
    var el = e.target && e.target.closest ? e.target.closest('a[data-confirm], button[data-confirm], input[data-confirm]') : null;
    if (!el || el.disabled) return;
    if (el.__tmsConfirmed) {
      el.__tmsConfirmed = false;
      return;
    }
    e.preventDefault();
    e.stopImmediatePropagation();
    confirmFor(el).then(function (ok) {
      if (!ok) return;
      if (el.tagName === 'A' && el.href) {
        if (el.target && el.target !== '_self') window.open(el.href, el.target);
        else window.location.href = el.href;
        return;
      }
      el.__tmsConfirmed = true;
      el.click();
    });
  }, true);

  // Forms that carry data-confirm: ask once, then send with the button that was clicked.
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || !form.matches || !form.matches('form[data-confirm]')) return;
    if (form.__tmsConfirmed) {
      form.__tmsConfirmed = false;
      return;
    }
    e.preventDefault();
    e.stopImmediatePropagation();
    var submitter = e.submitter || null;
    confirmFor(form, submitter).then(function (ok) {
      if (!ok) return;
      form.__tmsConfirmed = true;
      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
      } else {
        if (submitter && submitter.name) {
          var hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = submitter.name;
          hidden.value = submitter.value;
          form.appendChild(hidden);
        }
        form.submit();
      }
    });
  }, true);
})();
