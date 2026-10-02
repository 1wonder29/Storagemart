(function (window, $) {
    'use strict';

    function resetSearchableSelect(selectEl) {
        if (!selectEl || selectEl.dataset.searchableSelectInit !== '1') {
            return;
        }
        const wrapper = selectEl.closest('.searchable-select');
        if (wrapper && wrapper.parentNode) {
            wrapper.parentNode.insertBefore(selectEl, wrapper);
            wrapper.remove();
        }
        delete selectEl.dataset.searchableSelectInit;
        selectEl.classList.remove('searchable-select-native');
    }

    function initBulkSearchable(selectEl, placeholder, noResultsText) {
        resetSearchableSelect(selectEl);
        if (!selectEl || selectEl.disabled || !window.initSearchableSelect) {
            return null;
        }
        return window.initSearchableSelect(selectEl, {
            placeholder: placeholder || '-- Type to search --',
            noResultsText: noResultsText || 'No matches found'
        });
    }

    function initEmployeeSearchable(selectEl, placeholder) {
        return initBulkSearchable(selectEl, placeholder || '-- Type to search employee --', 'No employees found');
    }

    function initBranchSearchable() {
        return initBulkSearchable(
            document.getElementById('bulk_transfer_branch_id'),
            '-- Type to search branch --',
            'No branches found'
        );
    }

    window.initBulkTransferTickets = function (config) {
        const base = (config.base || '').replace(/\/$/, '');
        const routePrefix = config.routePrefix || 'aom';
        const allOperationsEmployees = config.allOperationsEmployees || [];
        let branchEmployees = [];

        function formatEmployeeName(emp, includeTicketCount) {
            let name = ((emp.lastname || '') + ', ' + (emp.firstname || '')).trim();
            if (emp.branchName) {
                name += ' (' + emp.branchName + ')';
            }
            if (includeTicketCount && emp.ticket_count) {
                name += ' — ' + emp.ticket_count + ' ticket' + (parseInt(emp.ticket_count, 10) === 1 ? '' : 's');
            }
            return name;
        }

        function populateSourceSelect() {
            const $source = $('#bulk_transfer_source_employee_id');
            const currentSource = ($source.val() || '').toString();

            $source.empty().append('<option value="">-- Select Employee --</option>');
            branchEmployees.forEach(function (emp) {
                const id = String(emp.employee_id || '');
                $source.append($('<option>', {
                    value: id,
                    text: formatEmployeeName(emp, true),
                    selected: id === currentSource
                }));
            });

            if (branchEmployees.length === 0) {
                $source.empty().append('<option value="">No employees with tickets in this branch</option>');
            }

            $source.prop('disabled', branchEmployees.length === 0);
            initEmployeeSearchable($source[0], '-- Type to search employee --');
        }

        function populateTargetSelect(excludeEmployeeId) {
            const excludeId = (excludeEmployeeId || '').toString();
            const $target = $('#bulk_transfer_employee_id');
            const currentTarget = ($target.val() || '').toString();

            $target.empty().append('<option value="">-- Select Employee --</option>');
            allOperationsEmployees.forEach(function (emp) {
                const id = String(emp.employee_id || '');
                if (id && id !== excludeId) {
                    $target.append($('<option>', {
                        value: id,
                        text: formatEmployeeName(emp),
                        selected: id === currentTarget
                    }));
                }
            });
            initEmployeeSearchable($target[0], '-- Type to search employee --');
        }

        function updateTransferPreview() {
            const branchId = ($('#bulk_transfer_branch_id').val() || '').toString();
            const employeeId = ($('#bulk_transfer_source_employee_id').val() || '').toString();
            const targetId = ($('#bulk_transfer_employee_id').val() || '').toString();
            const $wrap = $('#bulkTransferCountWrap');
            const $text = $('#bulkTransferCountText');
            const $submit = $('#bulkTransferSubmitBtn');

            $wrap.addClass('d-none').removeClass('alert-warning').addClass('alert-info');
            $text.text('');
            $submit.prop('disabled', true);

            if (!branchId || !employeeId || !targetId || employeeId === targetId) {
                return;
            }

            $.get(base + '/' + routePrefix + '/api/transferable-tickets-count', {
                branch_id: branchId,
                employee_id: employeeId
            }).done(function (res) {
                const count = parseInt(res && res.count, 10) || 0;
                $wrap.removeClass('d-none');
                if (count > 0) {
                    $wrap.removeClass('alert-warning').addClass('alert-info');
                    $text.text(count + ' ticket' + (count === 1 ? '' : 's') + ' will be transferred.');
                    $submit.prop('disabled', false);
                } else {
                    $wrap.removeClass('alert-info').addClass('alert-warning');
                    $text.text('No tickets found for this employee in the selected branch.');
                    $submit.prop('disabled', true);
                }
            }).fail(function () {
                $wrap.removeClass('d-none').removeClass('alert-info').addClass('alert-warning');
                $text.text('Unable to load ticket count. Please try again.');
            });
        }

        populateTargetSelect();
        initBranchSearchable();

        $('#bulk_transfer_branch_id').on('change', function () {
            const branchId = ($(this).val() || '').toString();
            branchEmployees = [];
            populateSourceSelect();
            populateTargetSelect($('#bulk_transfer_source_employee_id').val());
            $('#bulkTransferCountWrap').addClass('d-none').removeClass('alert-warning').addClass('alert-info');
            $('#bulkTransferSubmitBtn').prop('disabled', true);

            if (!branchId) {
                resetSearchableSelect(document.getElementById('bulk_transfer_source_employee_id'));
                $('#bulk_transfer_source_employee_id')
                    .prop('disabled', true)
                    .html('<option value="">-- Select branch first --</option>');
                return;
            }

            resetSearchableSelect(document.getElementById('bulk_transfer_source_employee_id'));
            $('#bulk_transfer_source_employee_id')
                .prop('disabled', true)
                .html('<option value="">Loading...</option>');

            $.get(base + '/' + routePrefix + '/api/employees-with-tickets-by-branch', { branch_id: branchId })
                .done(function (res) {
                    branchEmployees = (res && res.data) ? res.data : [];
                    populateSourceSelect();
                })
                .fail(function (xhr) {
                    branchEmployees = [];
                    const message = (xhr.responseJSON && xhr.responseJSON.error)
                        ? xhr.responseJSON.error
                        : 'Failed to load employees';
                    $('#bulk_transfer_source_employee_id')
                        .html('<option value="">' + message + '</option>');
                });
        });

        $('#bulk_transfer_source_employee_id').on('change', function () {
            populateTargetSelect($(this).val());
            updateTransferPreview();
        });

        $('#bulk_transfer_employee_id').on('change', updateTransferPreview);

        $('#bulkTransferForm').on('submit', function (e) {
            const branchId = ($('#bulk_transfer_branch_id').val() || '').toString();
            const sourceId = ($('#bulk_transfer_source_employee_id').val() || '').toString();
            const targetId = ($('#bulk_transfer_employee_id').val() || '').toString();
            if (!branchId || !sourceId || !targetId) {
                e.preventDefault();
                alert('Please select a branch and an employee from the search results for both Transfer From and Transfer To.');
            }
        });

        $('#bulkTransferModal').on('hidden.bs.modal', function () {
            $('#bulkTransferForm')[0].reset();
            branchEmployees = [];
            resetSearchableSelect(document.getElementById('bulk_transfer_branch_id'));
            resetSearchableSelect(document.getElementById('bulk_transfer_source_employee_id'));
            resetSearchableSelect(document.getElementById('bulk_transfer_employee_id'));
            $('#bulk_transfer_source_employee_id')
                .prop('disabled', true)
                .html('<option value="">-- Select branch first --</option>');
            populateTargetSelect();
            initBranchSearchable();
            $('#bulkTransferCountWrap').addClass('d-none').removeClass('alert-warning').addClass('alert-info');
            $('#bulkTransferSubmitBtn').prop('disabled', true);
        });
    };
})(window, jQuery);
