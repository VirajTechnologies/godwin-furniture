<script>
    (function () {
        var root = document.documentElement;
        var modeBtn = document.getElementById('appModeToggle');
        var swatchBtns = document.querySelectorAll('#appThemePicker .admin-swatch-btn');

        function syncSwatches(activeColor) {
            swatchBtns.forEach(function (btn) {
                var isActive = btn.getAttribute('data-theme-color') === activeColor;
                btn.classList.toggle('is-active', isActive);
                btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        syncSwatches(
            root.getAttribute('data-admin-theme')
            || localStorage.getItem('app_theme_color')
            || localStorage.getItem('admin_theme_color')
            || ''
        );

        swatchBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var color = btn.getAttribute('data-theme-color');
                root.setAttribute('data-admin-theme', color);
                localStorage.setItem('app_theme_color', color);
                localStorage.setItem('admin_theme_color', color);
                syncSwatches(color);
            });
        });

        if (modeBtn) {
            modeBtn.addEventListener('click', function () {
                var current = root.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
                var next = current === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-bs-theme', next);
                root.setAttribute('data-topbar', next);
                localStorage.setItem('app_theme_mode', next);
                localStorage.setItem('admin_theme_mode', next);
                sessionStorage.setItem('data-bs-theme', next);
                sessionStorage.setItem('data-topbar', next);
            });
        }
    })();
</script>
