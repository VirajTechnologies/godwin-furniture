<script>
    (function () {
        var savedMode = localStorage.getItem('app_theme_mode') || localStorage.getItem('admin_theme_mode');
        var savedColor = localStorage.getItem('app_theme_color') || localStorage.getItem('admin_theme_color');
        if (savedMode === 'dark' || savedMode === 'light') {
            document.documentElement.setAttribute('data-bs-theme', savedMode);
            document.documentElement.setAttribute('data-topbar', savedMode);
        }
        if (savedColor) {
            document.documentElement.setAttribute('data-admin-theme', savedColor);
        }
    })();
</script>
