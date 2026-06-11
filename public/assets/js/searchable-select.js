(function (window) {
    'use strict';

    function initSearchableSelect(selectEl, options) {
        if (!selectEl || selectEl.dataset.searchableSelectInit === '1') {
            return null;
        }

        options = options || {};
        var placeholder = options.placeholder || (selectEl.options[0] ? selectEl.options[0].text : 'Select...');
        var noResultsText = options.noResultsText || 'No matches found';

        var wrapper = document.createElement('div');
        wrapper.className = 'searchable-select';
        selectEl.parentNode.insertBefore(wrapper, selectEl);
        wrapper.appendChild(selectEl);
        selectEl.classList.add('searchable-select-native');
        selectEl.dataset.searchableSelectInit = '1';

        var input = document.createElement('input');
        input.type = 'text';
        input.className = selectEl.className.replace('searchable-select-native', '').trim() + ' searchable-select-input';
        input.placeholder = placeholder;
        input.autocomplete = 'off';
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-autocomplete', 'list');

        var list = document.createElement('div');
        list.className = 'searchable-select-dropdown';
        list.setAttribute('role', 'listbox');
        list.hidden = true;

        wrapper.appendChild(input);
        wrapper.appendChild(list);

        var activeIndex = -1;

        function getSelectableOptions() {
            return Array.from(selectEl.options).filter(function (option) {
                return option.value !== '';
            });
        }

        function setActiveOption(optionEl) {
            Array.from(list.querySelectorAll('.searchable-select-option')).forEach(function (item) {
                item.classList.remove('is-active');
            });
            if (optionEl) {
                optionEl.classList.add('is-active');
                optionEl.scrollIntoView({ block: 'nearest' });
            }
        }

        function selectOption(optionEl) {
            if (!optionEl || optionEl.classList.contains('is-empty')) {
                return;
            }

            selectEl.value = optionEl.dataset.value || '';
            input.value = optionEl.textContent || '';
            list.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
            selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function renderList(filterText) {
            var filter = (filterText || '').trim().toLowerCase();
            var matches = getSelectableOptions().filter(function (option) {
                return !filter || option.text.toLowerCase().indexOf(filter) !== -1;
            });

            list.innerHTML = '';
            activeIndex = -1;

            if (!matches.length) {
                var emptyItem = document.createElement('div');
                emptyItem.className = 'searchable-select-option is-empty';
                emptyItem.textContent = noResultsText;
                list.appendChild(emptyItem);
                list.hidden = false;
                input.setAttribute('aria-expanded', 'true');
                return;
            }

            matches.forEach(function (option, index) {
                var item = document.createElement('div');
                item.className = 'searchable-select-option';
                item.textContent = option.text;
                item.dataset.value = option.value;
                item.setAttribute('role', 'option');
                item.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    selectOption(item);
                });
                item.addEventListener('mouseenter', function () {
                    activeIndex = index;
                    setActiveOption(item);
                });
                list.appendChild(item);
            });

            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        function syncInputFromSelect() {
            var selected = selectEl.options[selectEl.selectedIndex];
            input.value = selected && selected.value ? selected.text : '';
        }

        input.addEventListener('focus', function () {
            renderList(input.value);
        });

        input.addEventListener('input', function () {
            selectEl.value = '';
            renderList(input.value);
        });

        input.addEventListener('keydown', function (event) {
            var items = Array.from(list.querySelectorAll('.searchable-select-option:not(.is-empty)'));

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (list.hidden) {
                    renderList(input.value);
                    items = Array.from(list.querySelectorAll('.searchable-select-option:not(.is-empty)'));
                }
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                setActiveOption(items[activeIndex]);
                return;
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                setActiveOption(items[activeIndex]);
                return;
            }

            if (event.key === 'Enter') {
                if (!list.hidden && activeIndex >= 0 && items[activeIndex]) {
                    event.preventDefault();
                    selectOption(items[activeIndex]);
                }
                return;
            }

            if (event.key === 'Escape') {
                list.hidden = true;
                input.setAttribute('aria-expanded', 'false');
                syncInputFromSelect();
            }
        });

        document.addEventListener('click', function (event) {
            if (!wrapper.contains(event.target)) {
                list.hidden = true;
                input.setAttribute('aria-expanded', 'false');
                syncInputFromSelect();
            }
        });

        syncInputFromSelect();

        return {
            input: input,
            select: selectEl,
            refresh: syncInputFromSelect
        };
    }

    window.initSearchableSelect = initSearchableSelect;
})(window);
