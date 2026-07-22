document.addEventListener("DOMContentLoaded", function() {
    const password = document.getElementById('password');
    const roleSelect = document.getElementById('id_rol');

    const idInput = document.getElementById('id_usuario');
    const idFeedback = document.getElementById('id_usuarioFeedback');

    const emailInput = document.getElementById('email');
    const emailFeedback = document.getElementById('emailFeedback');
    const emailHelp = document.getElementById('emailHelp');
    const idSpinner = document.getElementById('idSpinner');
    const emailSpinner = document.getElementById('emailSpinner');
    const form = document.querySelector('form[action="registro-submit"]');
    const submitBtn = document.querySelector('button[name="register"]');
    let emailValidationInProgress = false;
    let idValidationInProgress = false;
    let emailTimer = null;
    let idTimer = null;

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
        // Email y documento deben haber sido confirmados por la API (is-valid),
        // no solo "no tener is-invalid". Esto evita enviar antes de que la
        // validacion async termine y prevenir duplicados silenciosos.
        const emailIsValid = emailInput ? emailInput.classList.contains('is-valid') : false;
        const idIsValid = idInput ? idInput.classList.contains('is-valid') : false;

        const val = password ? password.value : '';
        const req = validatePasswordRequirements(val);
        const passwordIsValid = val.length > 0 && req.length && req.upper && req.lower && req.number && req.special;

        const phoneVal = document.getElementById('telefono') ? document.getElementById('telefono').value.trim() : '';
        const phoneIsValid = /^\d{10}$/.test(phoneVal);

        const allValid = emailIsValid && idIsValid && passwordIsValid && phoneIsValid;

        if (submitBtn) {
            submitBtn.disabled = !allValid;
            submitBtn.classList.toggle('disabled', !allValid);
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

    const validateDocumentRealtime = () => {
        if (!idInput) return;
        const id_usuario = idInput.value.trim();

        if (id_usuario.length === 0) {
            idInput.classList.remove('is-invalid', 'is-valid');
            if (idFeedback) idFeedback.style.display = '';
            idValidationInProgress = false;
            checkFormValidity();
            return;
        }

        if (!/^\d+$/.test(id_usuario)) {
            idInput.classList.add('is-invalid');
            idInput.classList.remove('is-valid');
            setIdInvalidMessage('El documento solo debe contener numeros.');
            idValidationInProgress = false;
            checkFormValidity();
            return;
        }

        // La columna id_usuario en BD es INT firmado (maximo 2,147,483,647).
        // Si el numero excede ese rango, mostramos error antes de enviar al servidor.
        if (parseInt(id_usuario, 10) > 2147483647) {
            idInput.classList.add('is-invalid');
            idInput.classList.remove('is-valid');
            setIdInvalidMessage('El numero de documento es demasiado grande. El maximo permitido es 2,147,483,647.');
            idValidationInProgress = false;
            checkFormValidity();
            return;
        }

        if (!/^\d{1,11}$/.test(id_usuario)) {
            idInput.classList.add('is-invalid');
            idInput.classList.remove('is-valid');
            setIdInvalidMessage('El documento debe tener maximo 11 digitos numericos.');
            idValidationInProgress = false;
            checkFormValidity();
            return;
        }

        idValidationInProgress = true;
        checkFormValidity();
        if (idSpinner) idSpinner.style.display = 'inline-block';

        const formData = new FormData();
        formData.append('id_usuario', id_usuario);

        fetch('check-document', {
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
            idValidationInProgress = false;
            if (idSpinner) idSpinner.style.display = 'none';
        });
    };

    if (idInput) {
        // Validacion en input (debounced) — igual que el email, para prevenir
        // que el usuario envie antes de que se verifique si el documento ya existe.
        idInput.addEventListener('input', function() {
            if (idTimer) {
                clearTimeout(idTimer);
            }
            idTimer = setTimeout(validateDocumentRealtime, 400);
        });

        idInput.addEventListener('blur', validateDocumentRealtime);
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

        fetch('check-email', {
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

    const phoneInput = document.getElementById('telefono');
    const phoneFeedback = document.getElementById('telefonoFeedback');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            const phoneVal = this.value.trim();
            if (phoneVal.length === 0) {
                phoneInput.classList.remove('is-invalid', 'is-valid');
                if (phoneFeedback) phoneFeedback.style.display = '';
            } else if (!/^\d{10}$/.test(phoneVal)) {
                phoneInput.classList.add('is-invalid');
                phoneInput.classList.remove('is-valid');
                if (phoneFeedback) {
                    phoneFeedback.textContent = phoneVal.length < 10
                        ? 'Faltan ' + (10 - phoneVal.length) + ' digitos. Debe tener exactamente 10 digitos.'
                        : 'El telefono debe tener exactamente 10 digitos.';
                    phoneFeedback.style.display = 'block';
                }
            } else {
                phoneInput.classList.remove('is-invalid');
                phoneInput.classList.add('is-valid');
                if (phoneFeedback) phoneFeedback.style.display = '';
            }
            checkFormValidity();
        });
    }

    // El flujo de submit para admin (rol 3) es manejado por los handlers de submit.
    // Los handlers ya retornan sin prevenir cuando el rol es 3, asi que no se necesita
    // un click handler especial que interfiera con el envio normal del formulario.

    const registrationForm = document.querySelector('.needs-validation');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(event) {
            const isAdminRole = roleSelect && roleSelect.value === '3';
            if (isAdminRole) {
                return;
            }
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
        const successModal = bootstrap.Modal.getOrCreateInstance(successModalEl);
        successModal.show();
    }
});
