(function () {
    function createEyeIcon(hidden) {
        if (hidden) {
            return [
                '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">',
                '<path d="M2.1 12s3.5-6.5 9.9-6.5S21.9 12 21.9 12s-3.5 6.5-9.9 6.5S2.1 12 2.1 12Z"></path>',
                '<circle cx="12" cy="12" r="3"></circle>',
                '</svg>',
            ].join('');
        }

        return [
            '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">',
            '<path d="M3 3l18 18"></path>',
            '<path d="M10.6 10.6A2 2 0 0 0 12 14a2 2 0 0 0 1.4-.6"></path>',
            '<path d="M7.4 7.4C4 9.2 2.1 12 2.1 12s3.5 6.5 9.9 6.5c1.8 0 3.4-.5 4.7-1.2"></path>',
            '<path d="M14 5.7c5.1.9 7.9 6.3 7.9 6.3s-.8 1.5-2.3 3"></path>',
            '</svg>',
        ].join('');
    }

    function enhancePasswordInput(input, index) {
        if (input.dataset.passwordToggleReady === 'true') {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'password-field';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        input.classList.add('password-field-input');
        input.dataset.passwordToggleReady = 'true';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'password-toggle-btn';
        button.setAttribute('aria-label', 'Mostrar contrasena');
        button.setAttribute('title', 'Mostrar contrasena');
        button.setAttribute('aria-pressed', 'false');
        button.innerHTML = createEyeIcon(true);
        wrapper.appendChild(button);

        const fieldId = input.id || 'password-field-' + index;
        input.id = fieldId;
        button.setAttribute('aria-controls', fieldId);

        button.addEventListener('click', function () {
            const shouldShow = input.getAttribute('type') === 'password';
            input.setAttribute('type', shouldShow ? 'text' : 'password');
            button.setAttribute('aria-label', shouldShow ? 'Ocultar contrasena' : 'Mostrar contrasena');
            button.setAttribute('title', shouldShow ? 'Ocultar contrasena' : 'Mostrar contrasena');
            button.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
            button.innerHTML = createEyeIcon(!shouldShow);
            input.focus();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[type="password"]').forEach(enhancePasswordInput);
    });
})();
