<script>
    (function () {
        var tokenUrl = @json(route('csrf.token'));
        var loginUrl = @json(route('admin.login'));
        var requiresAuth = @json((bool) ($requiresAuth ?? false));
        var keepAliveMs = 15 * 60 * 1000;

        function applyToken(token) {
            if (!token) {
                return;
            }

            document.querySelectorAll('meta[name="csrf-token"]').forEach(function (meta) {
                meta.setAttribute('content', token);
            });

            document.querySelectorAll('input[name="_token"]').forEach(function (input) {
                input.value = token;
            });
        }

        function refreshToken() {
            return fetch(tokenUrl, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error('Unable to refresh the session token.');
                }

                return response.json();
            }).then(function (data) {
                applyToken(data.token);

                return data;
            });
        }

        function keepAlive() {
            refreshToken().then(function (data) {
                if (requiresAuth && data && data.authenticated === false) {
                    window.location.assign(loginUrl);
                }
            }).catch(function () {});
        }

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                keepAlive();
            }
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                keepAlive();
            }
        });

        window.setInterval(keepAlive, keepAliveMs);

        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (!(form instanceof HTMLFormElement) || form.dataset.csrfFresh === '1') {
                return;
            }

            if ((form.getAttribute('method') || 'get').toLowerCase() !== 'post') {
                return;
            }

            if (!form.querySelector('input[name="_token"]')) {
                return;
            }

            event.preventDefault();

            var submitButton = form.querySelector('[type="submit"]');

            if (submitButton) {
                submitButton.disabled = true;
            }

            refreshToken().then(function (data) {
                if (requiresAuth && data && data.authenticated === false) {
                    window.location.assign(loginUrl);

                    return;
                }

                form.dataset.csrfFresh = '1';
                HTMLFormElement.prototype.submit.call(form);
            }).catch(function () {
                form.dataset.csrfFresh = '1';
                HTMLFormElement.prototype.submit.call(form);
            });
        });
    })();
</script>
