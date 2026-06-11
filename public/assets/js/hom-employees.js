(function ($) {
    'use strict';

    function isTableInitialized(table) {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
            return true;
        }
        if (typeof DataTable !== 'undefined' && DataTable.isDataTable) {
            return DataTable.isDataTable(table);
        }
        return false;
    }

    function getTableId(settings) {
        return (
            settings.sTableId ||
            (settings.nTable && settings.nTable.id) ||
            (settings.nTable && settings.nTable.getAttribute && settings.nTable.getAttribute('id')) ||
            ''
        );
    }

    function registerSearch(fn) {
        if (typeof DataTable !== 'undefined' && DataTable.ext && DataTable.ext.search) {
            DataTable.ext.search.push(fn);
        } else if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.search) {
            $.fn.dataTable.ext.search.push(fn);
        }
    }

    function initTransferButtons() {
        const transferModal = $('#transferBranchModal');
        const transferEmployeeId = document.getElementById('transferEmployeeId');
        const transferEmployeeName = document.getElementById('transferEmployeeName');
        const transferCurrentBranch = document.getElementById('transferCurrentBranch');
        const transferBranchId = document.getElementById('transferBranchId');

        $(document).on('click', '.transfer-branch-btn', function () {
            const employeeId = this.dataset.employeeId || '';
            const employeeName = this.dataset.employeeName || '';
            const currentBranchId = this.dataset.currentBranchId || '';
            const currentBranchName = this.dataset.currentBranchName || 'Unassigned';

            if (transferEmployeeId) {
                transferEmployeeId.value = employeeId;
            }
            if (transferEmployeeName) {
                transferEmployeeName.textContent = employeeName;
            }
            if (transferCurrentBranch) {
                transferCurrentBranch.textContent = currentBranchName;
            }
            if (transferBranchId) {
                Array.from(transferBranchId.options).forEach((option) => {
                    option.hidden = option.value !== '' && option.value === currentBranchId;
                });
                transferBranchId.value = '';
            }

            transferModal.modal('show');
        });
    }

    function initHomEmployeesTable() {
        const $table = $('#homEmployeesTable');
        if (!$table.length || !$table.find('tbody tr').length) {
            initTransferButtons();
            return;
        }
        if (isTableInitialized($table[0])) {
            return;
        }

        const dt = new DataTable('#homEmployeesTable', {
            fixedHeader: { header: true },
            order: [[0, 'asc']],
            pageLength: 10,
            columnDefs: [{ targets: [5], orderable: false, searchable: false }],
        });

        let branchFilter = '';

        registerSearch(function (settings, searchData, dataIndex) {
            if (getTableId(settings) !== 'homEmployeesTable') {
                return true;
            }

            const row = dt.row(dataIndex).node();
            if (!row) {
                return true;
            }

            const branchId = (row.getAttribute('data-branch-id') || '').trim();
            if (branchFilter && branchId !== branchFilter) {
                return false;
            }
            return true;
        });

        function redraw() {
            dt.draw();
        }

        $('#homBranchFilter').on('change', function () {
            branchFilter = ($(this).val() || '').trim();
            redraw();
        });

        $('#homSearchFilter').on('input', function () {
            dt.search($(this).val() || '').draw();
        });

        $('#homClearFilters').on('click', function () {
            branchFilter = '';
            $('#homBranchFilter').val('');
            $('#homSearchFilter').val('');
            dt.search('');
            redraw();
        });

        initTransferButtons();
    }

    $(document).ready(initHomEmployeesTable);
})(jQuery);
