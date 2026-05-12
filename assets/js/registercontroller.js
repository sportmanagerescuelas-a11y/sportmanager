document.addEventListener("DOMContentLoaded", function() {
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    if (togglePassword && password) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            const iconSvg = this.querySelector('svg');
            if (iconSvg) {
                if (type === 'password') {
                    iconSvg.innerHTML = '<path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>';
                    iconSvg.classList.remove('bi-eye-slash-fill');
                    iconSvg.classList.add('bi-eye-fill');
                } else {
                    iconSvg.innerHTML = '<path d="m10.79 12.912-1.614-1.614a3.573 3.573 0 0 1-4.387-4.387L1.447 2.82A.5.5 0 0 1 2.12 2.146L14.854 14.854a.5.5 0 0 1-.678.678l-1.614-1.614zM14.414 5.914a.5.5 0 0 1 .096.707L12.12 9.12a3.5 3.5 0 0 1-4.387 4.387L5.914 14.414a.5.5 0 0 1-.707-.096L2.146 12.12a.5.5 0 0 1 .096-.707L4.88 9.12a3.5 3.5 0 0 1 4.387-4.387L12.12 2.146a.5.5 0 0 1 .707.096L14.414 5.914zM11.5 8a3.5 3.5 0 0 0-3.5-3.5c-.989 0-1.895.44-2.512 1.1A3.5 3.5 0 0 0 4.5 8c0 .989.44 1.895 1.1 2.512A3.5 3.5 0 0 0 8 11.5c.989 0 1.895-.44 2.512-1.1A3.5 3.5 0 0 0 11.5 8z"/>';
                    iconSvg.classList.remove('bi-eye-fill');
                    iconSvg.classList.add('bi-eye-slash-fill');
                }
            }
        });
    }

    const idInput = document.getElementById('id_usuario');
    const idFeedback = document.getElementById('documentFeedback');

    const emailInput = document.getElementById('email');
    const emailFeedback = document.getElementById('emailFeedback');
    const idSpinner = document.getElementById('idSpinner');
    const emailSpinner = document.getElementById('emailSpinner');
    const submitBtn = document.querySelector('button[name="register"]');

    const validatePasswordRequirements = (val) => {
        return {
            length: val.length >= 8,
            upper: /[A-Z]/.test(val),
            lower: /[a-z]/.test(val),
            number: /\d/.test(val),
            special: /[@$!%*?&._\-]/.test(val)
        };
    };

    const checkFormValidity = () => {
        const emailIsInvalid = emailInput ? emailInput.classList.contains('is-invalid') : false;
        const idIsInvalid = idInput ? idInput.classList.contains('is-invalid') : false;

        const val = password ? password.value : '';
        const req = validatePasswordRequirements(val);
        const passwordIsValid = req.length && req.upper && req.lower && req.number && req.special;

        if (submitBtn) {
            submitBtn.disabled = emailIsInvalid || idIsInvalid || !passwordIsValid;
        }
    };

    const updateReq = (id, passed, text) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.className = passed ? 'text-success' : 'text-danger';
        el.textContent = (passed ? '? ' : '? ') + text;
    };

    const updatePasswordStatus = (val) => {
        const req = validatePasswordRequirements(val);

        updateReq('req-length', req.length, 'Minimo 8 caracteres');
        updateReq('req-upper', req.upper, 'Una letra mayuscula');
        updateReq('req-lower', req.lower, 'Una letra minuscula');
        updateReq('req-number', req.number, 'Un numero');
        updateReq('req-special', req.special, 'Un caracter especial (@$!%*?&._-)');

        const missing = [];
        if (!req.length) missing.push('minimo 8 caracteres');
        if (!req.upper) missing.push('una mayuscula');
        if (!req.lower) missing.push('una minuscula');
        if (!req.number) missing.push('un numero');
        if (!req.special) missing.push('un caracter especial');

        const passwordMissing = document.getElementById('passwordMissing');
        if (passwordMissing) {
            if (val.length === 0) {
                passwordMissing.textContent = '';
                passwordMissing.className = 'mt-2 small text-danger';
            } else if (missing.length > 0) {
                passwordMissing.textContent = 'Te falta: ' + missing.join(', ') + '.';
                passwordMissing.className = 'mt-2 small text-danger';
            } else {
                passwordMissing.textContent = 'La contrasena cumple todos los requisitos.';
                passwordMissing.className = 'mt-2 small text-success';
            }
        }
    };

    if (password) {
        password.addEventListener('input', function() {
            updatePasswordStatus(this.value);
            checkFormValidity();
        });

        updatePasswordStatus(password.value);
    }

    if (idInput) {
        idInput.addEventListener('blur', function() {
            const id_usuario = this.value;
            if (id_usuario.length > 0) {
                if (idSpinner) idSpinner.style.display = 'inline-block';

                const formData = new FormData();
                formData.append('id_usuario', id_usuario);

                fetch('controller/checkDocumentController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (idSpinner) idSpinner.style.display = 'none';

                    if (idFeedback) {
                        if (data.exists) {
                            idFeedback.style.display = 'block';
                            idInput.classList.add('is-invalid');
                        } else {
                            idFeedback.style.display = 'none';
                            idInput.classList.remove('is-invalid');
                        }
                    }
                    checkFormValidity();
                })
                .catch(error => {
                    if (idSpinner) idSpinner.style.display = 'none';
                    console.error('Error:', error);
                });
            }
        });
    }

    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value;
            if (email.length > 0 && email.includes('@')) {
                if (emailSpinner) emailSpinner.style.display = 'inline-block';

                const formData = new FormData();
                formData.append('email', email);

                fetch('controller/checkEmailController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (emailSpinner) emailSpinner.style.display = 'none';

                    if (emailFeedback) {
                        if (data.exists) {
                            emailFeedback.style.display = 'block';
                            emailInput.classList.add('is-invalid');
                        } else {
                            emailFeedback.style.display = 'none';
                            emailInput.classList.remove('is-invalid');
                        }
                    }
                    checkFormValidity();
                })
                .catch(error => {
                    if (emailSpinner) emailSpinner.style.display = 'none';
                    console.error('Error:', error);
                });
            }
        });
    }

    const registrationForm = document.querySelector('.needs-validation');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(event) {
            checkFormValidity();
            if (!registrationForm.checkValidity() || (submitBtn && submitBtn.disabled)) {
                event.preventDefault();
                event.stopPropagation();
            }
            registrationForm.classList.add('was-validated');
        }, false);
    }

    checkFormValidity();

    const successModalEl = document.getElementById('successModal');
    if (successModalEl) {
        const successModal = new bootstrap.Modal(successModalEl);
        successModal.show();
    }
});
