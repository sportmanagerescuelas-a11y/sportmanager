document.addEventListener("DOMContentLoaded", function() {
    const password = document.getElementById('password');

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
