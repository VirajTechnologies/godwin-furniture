@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stateSelect = document.getElementById('state_id');
            const districtSelect = document.getElementById('district_id');
            const citySelect = document.getElementById('city_id');

            if (!stateSelect || !districtSelect) {
                return;
            }

            const districtUrl = @json(route('admin.districts.options'));
            const cityUrl = @json(route('admin.cities.options'));

            function placeholder(select, label) {
                select.innerHTML = '';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = label;
                select.appendChild(option);
            }

            function fill(select, items, labelKey, placeholderLabel, selected) {
                placeholder(select, placeholderLabel);
                items.forEach(function (item) {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item[labelKey];
                    if (String(item.id) === String(selected)) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            }

            stateSelect.addEventListener('change', function () {
                placeholder(districtSelect, 'Select District');
                if (citySelect) {
                    placeholder(citySelect, 'Select City');
                }
                if (!stateSelect.value) {
                    return;
                }
                fetch(districtUrl + '?state_id=' + encodeURIComponent(stateSelect.value), {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (response) { return response.json(); })
                    .then(function (items) {
                        fill(districtSelect, items, 'district_name', 'Select District');
                    });
            });

            districtSelect.addEventListener('change', function () {
                if (!citySelect) {
                    return;
                }
                placeholder(citySelect, 'Select City');
                if (!districtSelect.value) {
                    return;
                }
                fetch(cityUrl + '?district_id=' + encodeURIComponent(districtSelect.value), {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (response) { return response.json(); })
                    .then(function (items) {
                        fill(citySelect, items, 'city_name', 'Select City');
                    });
            });
        });
    </script>
@endpush
