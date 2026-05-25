document.addEventListener("DOMContentLoaded", function() {
    const password = document.getElementById('password');

    const idInput = document.getElementById('id_usuario');
    const idFeedback = document.getElementById('id_usuarioFeedback');

    const emailInput = document.getElementById('email');
    const emailFeedback = document.getElementById('emailFeedback');
    const emailHelp = document.getElementById('emailHelp');
    const idSpinner = document.getElementById('idSpinner');
    const emailSpinner = document.getElementById('emailSpinner');
    const submitBtn = document.querySelector('button[name="register"]');
    let emailValidationInProgress = false;
    let emailTimer = null;

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
            submitBtn.disabled = emailIsInvalid || idIsInvalid || !passwordIsValid || emailValidationInProgress;
        }
    };

    const setEmailHelp = (text, isSuccess) => {
        if (!emailHelp) return;
        emailHelp.textContent = text;
        emailHelp.className = isSuccess ? 'form-text text-success' : 'form-text text-danger';
    };

    const setEmailInvalidMessage = (message) => {
        if (!emailFeedback) return;
        emailFeedback.textContent = message;
        emailFeedback.style.display = 'block';
    };

    const setIdInvalidMessage = (message) => {
        if (!idFeedback) return;
        idFeedback.textContent = message;
        idFeedback.style.display = 'block';
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
        idInput.addEventListener('blur', function () {
            const id_usuario = this.value.trim();
            if (id_usuario.length === 0) {
                idInput.classList.remove('is-invalid', 'is-valid');
                if (idFeedback) {
                    idFeedback.style.display = '';
                }
                checkFormValidity();
                return;
            }

            if (!/^\d+$/.test(id_usuario)) {
                idInput.classList.add('is-invalid');
                idInput.classList.remove('is-valid');
                setIdInvalidMessage('El documento solo debe contener numeros.');
                checkFormValidity();
                return;
            }

            if (idSpinner) idSpinner.style.display = 'inline-block';

            const formData = new FormData();
            formData.append('id_usuario', id_usuario);

            fetch('controllers/checkDocumentController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.valid && data.exists === false) {
                    idInput.classList.remove('is-invalid');
                    idInput.classList.add('is-valid');
                    if (idFeedback) idFeedback.style.display = '';
                } else {
                    idInput.classList.add('is-invalid');
                    idInput.classList.remove('is-valid');
                    setIdInvalidMessage((data && data.message) ? data.message : 'No se pudo validar el documento.');
                }
                checkFormValidity();
            })
            .catch(() => {
                idInput.classList.add('is-invalid');
                idInput.classList.remove('is-valid');
                setIdInvalidMessage('No se pudo validar el documento. Intenta de nuevo.');
                checkFormValidity();
            })
            .finally(() => {
                if (idSpinner) idSpinner.style.display = 'none';
            });
        });
    }

    const validateEmailRealtime = () => {
        if (!emailInput) return;
        const email = emailInput.value.trim();

        if (email === '') {
            emailInput.classList.remove('is-invalid', 'is-valid');
            if (emailFeedback) {
                emailFeedback.textContent = 'Este correo ya esta registrado.';
                emailFeedback.style.display = '';
            }
            if (emailHelp) emailHelp.textContent = '';
            if (emailSpinner) emailSpinner.style.display = 'none';
            emailValidationInProgress = false;
            checkFormValidity();
            return;
        }

        const basicEmailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!basicEmailRegex.test(email)) {
            emailInput.classList.add('is-invalid');
            emailInput.classList.remove('is-valid');
            setEmailInvalidMessage('El formato del correo no es valido.');
            setEmailHelp('', false);
            if (emailSpinner) emailSpinner.style.display = 'none';
            emailValidationInProgress = false;
            checkFormValidity();
            return;
        }

        emailValidationInProgress = true;
        checkFormValidity();
        if (emailSpinner) emailSpinner.style.display = 'inline-block';

        const formData = new FormData();
        formData.append('email', email);

        fetch('controllers/checkEmailController.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data && data.valid && data.exists === false) {
                    emailInput.classList.remove('is-invalid');
                    emailInput.classList.add('is-valid');
                    if (emailFeedback) {
                        emailFeedback.textContent = 'Este correo ya esta registrado.';
                        emailFeedback.style.display = '';
                    }
                    setEmailHelp('Correo valido y disponible.', true);
                } else {
                    emailInput.classList.add('is-invalid');
                    emailInput.classList.remove('is-valid');
                    setEmailInvalidMessage((data && data.message) ? data.message : 'No se pudo validar el correo.');
                    setEmailHelp('', false);
                }
            })
            .catch(() => {
                emailInput.classList.add('is-invalid');
                emailInput.classList.remove('is-valid');
                setEmailInvalidMessage('No se pudo validar el correo. Intenta de nuevo.');
                setEmailHelp('', false);
            })
            .finally(() => {
                emailValidationInProgress = false;
                if (emailSpinner) emailSpinner.style.display = 'none';
                checkFormValidity();
            });
    };

    if (emailInput) {
        emailInput.addEventListener('input', function() {
            if (emailTimer) {
                clearTimeout(emailTimer);
            }
            emailTimer = setTimeout(validateEmailRealtime, 350);
        });

        emailInput.addEventListener('blur', validateEmailRealtime);
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
