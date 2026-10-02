(function () {
  'use strict';

  var category = document.getElementById('category');
  var subject = document.getElementById('subject');
  var description = document.getElementById('concern_details');
  var priority = document.getElementById('priority');
  if (!category) return;

  function parseList(attr) {
    try {
      return JSON.parse(category.getAttribute(attr) || '[]');
    } catch (e) {
      return [];
    }
  }

  var headOfficeList = parseList('data-head-office');
  var branchList = parseList('data-branch');

  /* ---------- Category list follows the selected branch ---------- */

  function currentBranchText() {
    var select = document.getElementById('branch_id');
    if (select && select.tagName === 'SELECT') {
      var option = select.options[select.selectedIndex];
      if (option && option.value) return option.text;
    }
    var display = document.getElementById('branchNameDisplay') || document.getElementById('branchName');
    if (display && display.value) return display.value;
    return null;
  }

  function isHeadOffice(text) {
    return /head\s*office/i.test(text) || /\(HO\)\s*$/i.test(String(text).trim());
  }

  function renderCategories(list) {
    var previous = category.value;
    category.innerHTML = '';

    var placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = '-- Select Category --';
    category.appendChild(placeholder);

    list.forEach(function (name) {
      var option = document.createElement('option');
      option.value = name;
      option.textContent = name;
      category.appendChild(option);
    });

    category.value = list.indexOf(previous) !== -1 ? previous : '';
    updatePreview();
  }

  function refreshCategories() {
    var text = currentBranchText();
    if (text === null) return;
    renderCategories(isHeadOffice(text) ? headOfficeList : branchList);
  }

  ['branch_id', 'branchNameDisplay', 'branchName'].forEach(function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('change', refreshCategories);
    el.addEventListener('input', refreshCategories);
  });
  document.addEventListener('tms:branch-changed', refreshCategories);

  /* ---------- Live preview ---------- */

  function setText(id, value) {
    var el = document.getElementById(id);
    if (!el) return;
    var text = (value || '').trim();
    if (text) {
      el.textContent = text;
      el.classList.remove('is-placeholder');
    } else {
      el.textContent = el.getAttribute('data-placeholder') || '';
      el.classList.add('is-placeholder');
    }
  }

  function truncate(text, max) {
    text = (text || '').replace(/\s+/g, ' ').trim();
    return text.length > max ? text.slice(0, max).trim() + '…' : text;
  }

  function toggleRow(rowId, valueId, value) {
    var row = document.getElementById(rowId);
    var target = document.getElementById(valueId);
    if (!row || !target) return;
    target.textContent = value;
    row.hidden = !value;
  }

  function updatePreview() {
    setText('previewSubject', subject ? subject.value : '');
    setText('previewDescription', description ? truncate(description.value, 140) : '');

    var priorityEl = document.getElementById('previewPriority');
    if (priorityEl && priority) priorityEl.textContent = priority.value || 'Medium';
    toggleRow('previewCategoryRow', 'previewCategory', category.value);
    toggleRow('previewCategoryRow', 'previewCategory', category.value);
  }

  [subject, description, priority, category].forEach(function (el) {
    if (!el) return;
    el.addEventListener('input', updatePreview);
    el.addEventListener('change', updatePreview);
  });

  refreshCategories();
  updatePreview();
})();
