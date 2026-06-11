(function () {
    const branchFilter = document.getElementById('homBranchFilter');
    const searchFilter = document.getElementById('homSearchFilter');
    const clearFiltersBtn = document.getElementById('homClearFilters');
    const tableRows = Array.from(document.querySelectorAll('#homEmployeesTable tbody tr'));
    const transferButtons = document.querySelectorAll('.transfer-branch-btn');
    const transferModal = $('#transferBranchModal');
    const transferEmployeeId = document.getElementById('transferEmployeeId');
    const transferEmployeeName = document.getElementById('transferEmployeeName');
    const transferCurrentBranch = document.getElementById('transferCurrentBranch');
    const transferBranchId = document.getElementById('transferBranchId');

    function applyFilters() {
        const branchValue = branchFilter ? branchFilter.value : '';
        const searchValue = (searchFilter ? searchFilter.value : '').trim().toLowerCase();

        tableRows.forEach((row) => {
            const rowBranchId = String(row.dataset.branchId || '');
            const rowSearch = row.dataset.search || '';
            const matchesBranch = !branchValue || rowBranchId === branchValue;
            const matchesSearch = !searchValue || rowSearch.includes(searchValue);
            row.style.display = matchesBranch && matchesSearch ? '' : 'none';
        });
    }

    if (branchFilter) {
        branchFilter.addEventListener('change', applyFilters);
    }

    if (searchFilter) {
        searchFilter.addEventListener('input', applyFilters);
    }

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function () {
            if (branchFilter) {
                branchFilter.value = '';
            }
            if (searchFilter) {
                searchFilter.value = '';
            }
            applyFilters();
        });
    }

    transferButtons.forEach((button) => {
        button.addEventListener('click', function () {
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
    });
})();
