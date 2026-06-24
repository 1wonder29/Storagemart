(function (window) {
  'use strict';

  function debounce(fn, delay) {
    var timer;
    return function () {
      var args = arguments;
      var context = this;
      clearTimeout(timer);
      timer = setTimeout(function () {
        fn.apply(context, args);
      }, delay);
    };
  }

  function hideSuggestions(list) {
    list.hidden = true;
    list.innerHTML = '';
  }

  function applyEmployeeSelection(fields, employee) {
    fields.hiddenId.value = employee.employee_id || '';
    fields.input.value = employee.full_name || '';
    if (fields.branchInput) {
      fields.branchInput.value = employee.branchName || '';
    }
    hideSuggestions(fields.list);
  }

  function renderSuggestions(fields, results) {
    var list = fields.list;
    list.innerHTML = '';

    if (!results.length) {
      var empty = document.createElement('div');
      empty.className = 'employee-search-suggestion is-empty';
      empty.textContent = 'No employees found';
      list.appendChild(empty);
      list.hidden = false;
      return;
    }

    results.forEach(function (employee, index) {
      var item = document.createElement('button');
      item.type = 'button';
      item.className = 'employee-search-suggestion';
      item.setAttribute('role', 'option');
      item.dataset.index = String(index);

      var name = document.createElement('span');
      name.className = 'employee-search-suggestion-name';
      name.textContent = employee.full_name || '';

      var meta = document.createElement('span');
      meta.className = 'employee-search-suggestion-meta';
      var metaParts = [];
      if (employee.employee_id) {
        metaParts.push('ID ' + employee.employee_id);
      }
      if (employee.branchName) {
        metaParts.push(employee.branchName);
      }
      meta.textContent = metaParts.join(' · ');

      item.appendChild(name);
      item.appendChild(meta);

      item.addEventListener('mousedown', function (event) {
        event.preventDefault();
        applyEmployeeSelection(fields, employee);
      });

      list.appendChild(item);
    });

    list.hidden = false;
  }

  function fetchSuggestions(fields, query) {
    var url = fields.suggestUrl + '?q=' + encodeURIComponent(query);

    fetch(url, { credentials: 'same-origin' })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Suggestion request failed');
        }
        return response.json();
      })
      .then(function (data) {
        if (!data.success) {
          hideSuggestions(fields.list);
          return;
        }
        fields.latestResults = data.results || [];
        renderSuggestions(fields, fields.latestResults);
      })
      .catch(function () {
        hideSuggestions(fields.list);
      });
  }

  function searchExactEmployee(fields) {
    var query = fields.input.value.trim();
    if (!query) {
      window.alert('Enter employee id or name');
      return;
    }

    var url = fields.searchUrl + '?q=' + encodeURIComponent(query);

    fetch(url, { credentials: 'same-origin' })
      .then(function (response) {
        if (!response.ok) {
          return response.text().then(function (text) {
            throw new Error(text || 'Search failed');
          });
        }
        return response.json();
      })
      .then(function (data) {
        if (data.success) {
          applyEmployeeSelection(fields, {
            employee_id: data.employee_id,
            full_name: data.full_name || data.fullName || '',
            branchName: data.branchName || '',
          });
          return;
        }
        window.alert(data.message || 'Employee not found');
      })
      .catch(function (error) {
        console.error(error);
        window.alert('Error contacting server.');
      });
  }

  function initEmployeeSearchAutocomplete(root) {
    if (!root || root.dataset.employeeSearchInit === '1') {
      return;
    }

    var input = root.querySelector('[data-employee-search-input]');
    var hiddenId = root.querySelector('[data-employee-id-input]');
    var branchInput = root.querySelector('[data-employee-branch-input]');
    var list = root.querySelector('[data-employee-suggestions]');
    var searchButton = root.querySelector('[data-employee-search-button]');
    var suggestUrl = root.dataset.suggestUrl || '';
    var searchUrl = root.dataset.searchUrl || '';

    if (!input || !hiddenId || !list || !suggestUrl || !searchUrl) {
      return;
    }

    root.dataset.employeeSearchInit = '1';

    var fields = {
      root: root,
      input: input,
      hiddenId: hiddenId,
      branchInput: branchInput,
      list: list,
      suggestUrl: suggestUrl,
      searchUrl: searchUrl,
      latestResults: [],
      activeIndex: -1,
    };

    var debouncedSuggest = debounce(function () {
      var query = input.value.trim();
      hiddenId.value = '';
      if (branchInput) {
        branchInput.value = '';
      }

      if (query.length < 1) {
        hideSuggestions(list);
        return;
      }

      fetchSuggestions(fields, query);
    }, 250);

    input.addEventListener('input', debouncedSuggest);

    input.addEventListener('focus', function () {
      var query = input.value.trim();
      if (query.length >= 1) {
        fetchSuggestions(fields, query);
      }
    });

    input.addEventListener('keydown', function (event) {
      var items = Array.from(list.querySelectorAll('.employee-search-suggestion:not(.is-empty)'));

      if (event.key === 'ArrowDown') {
        if (!items.length) {
          return;
        }
        event.preventDefault();
        fields.activeIndex = Math.min(fields.activeIndex + 1, items.length - 1);
        items.forEach(function (item, index) {
          item.classList.toggle('is-active', index === fields.activeIndex);
        });
        return;
      }

      if (event.key === 'ArrowUp') {
        if (!items.length) {
          return;
        }
        event.preventDefault();
        fields.activeIndex = Math.max(fields.activeIndex - 1, 0);
        items.forEach(function (item, index) {
          item.classList.toggle('is-active', index === fields.activeIndex);
        });
        return;
      }

      if (event.key === 'Enter') {
        if (!list.hidden && fields.activeIndex >= 0 && fields.latestResults[fields.activeIndex]) {
          event.preventDefault();
          applyEmployeeSelection(fields, fields.latestResults[fields.activeIndex]);
        }
        return;
      }

      if (event.key === 'Escape') {
        hideSuggestions(list);
        fields.activeIndex = -1;
      }
    });

    if (searchButton) {
      searchButton.addEventListener('click', function () {
        searchExactEmployee(fields);
      });
    }

    document.addEventListener('click', function (event) {
      if (!root.contains(event.target)) {
        hideSuggestions(list);
        fields.activeIndex = -1;
      }
    });
  }

  window.initEmployeeSearchAutocomplete = initEmployeeSearchAutocomplete;

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-employee-search]').forEach(initEmployeeSearchAutocomplete);
  });
})(window);
