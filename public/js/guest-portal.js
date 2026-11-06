/**
 * SIKAP FKp UNRI — Strict CSP Guest Portal Controller
 * Zero unsafe-eval, zero unsafe-inline.
 */
document.addEventListener('DOMContentLoaded', function () {
    // 1. Toggle Show/Hide Password
    var togglePwdBtn = document.querySelector('[data-toggle-password]');
    if (togglePwdBtn) {
        togglePwdBtn.addEventListener('click', function () {
            var pwdInput = document.getElementById('password');
            var eyeOpen = document.getElementById('icon-eye-open');
            var eyeClosed = document.getElementById('icon-eye-closed');
            if (!pwdInput) return;

            var isHidden = pwdInput.getAttribute('type') === 'password';
            pwdInput.setAttribute('type', isHidden ? 'text' : 'password');

            if (eyeOpen && eyeClosed) {
                eyeOpen.classList.toggle('hidden', isHidden);
                eyeClosed.classList.toggle('hidden', !isHidden);
            }
        });
    }

    // 2. Modal Open Triggers
    document.querySelectorAll('[data-open-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modalId = btn.getAttribute('data-open-modal');
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
            }
        });
    });

    // 3. Modal Close Triggers
    document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modalId = btn.getAttribute('data-close-modal');
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
            }
            if (btn.hasAttribute('data-focus-login')) {
                setTimeout(function () {
                    var loginInput = document.getElementById('login');
                    if (loginInput) loginInput.focus();
                }, 100);
            }
        });
    });

    // 4. Public Document Verification Redirect
    var verifyInput = document.getElementById('verify-code-input');
    var verifySubmitBtn = document.getElementById('verify-code-submit');

    function submitVerifyCode() {
        if (!verifyInput) return;
        var code = (verifyInput.value || '').trim();
        if (code) {
            window.location.href = '/verifikasi-dokumen/' + encodeURIComponent(code);
        }
    }

    if (verifySubmitBtn) {
        verifySubmitBtn.addEventListener('click', submitVerifyCode);
    }

    if (verifyInput) {
        verifyInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitVerifyCode();
            }
        });
    }

    // 5. Offline Reload Button
    document.querySelectorAll('[data-action="reload"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            window.location.reload();
        });
    });
});
