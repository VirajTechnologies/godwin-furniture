@if (($part ?? 'scripts') === 'styles')
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@else
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
        jQuery(function ($) {
            $('.js-datatable').each(function () {
                const $table = $(this);
                if ($.fn.dataTable.isDataTable(this)) {
                    return;
                }

                const skipped = String($table.data('unsearchable') || '')
                    .split(',')
                    .map(function (value) { return value.trim(); })
                    .filter(function (value) { return value !== ''; })
                    .map(function (value) { return Number(value); })
                    .filter(function (value) { return Number.isInteger(value); });
                const label = $table.data('label') || 'records';
                const dataTable = $table.DataTable({
                    pageLength: 10,
                    order: [[Number($table.data('orderColumn') ?? 1), $table.data('orderDir') || 'asc']],
                    columnDefs: skipped.length ? [{ orderable: false, searchable: false, targets: skipped }] : [],
                    language: {
                        search: 'Search:',
                        lengthMenu: 'Show _MENU_ ' + label,
                        info: 'Showing _START_ to _END_ of _TOTAL_ ' + label,
                        infoEmpty: 'No ' + label,
                        zeroRecords: 'No ' + label + ' match this search',
                    },
                });

                if (String($table.data('serial') ?? '1') === '0') {
                    return;
                }

                const serialColumn = Number($table.data('serialColumn') ?? 0);
                const numberRows = function () {
                    const start = dataTable.page.info().start;
                    dataTable.column(serialColumn, { page: 'current' }).nodes().each(function (cell, index) {
                        cell.textContent = String(start + index + 1);
                    });
                };
                dataTable.on('draw', numberRows);
                numberRows();
            });
        });
    </script>
@endif
