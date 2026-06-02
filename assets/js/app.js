(function () {
    const table = document.getElementById('deportistasTable');
    const perPageSelect = document.getElementById('perPage');
    const submitBtn = document.getElementById('submitAsistencia');
    const clearBtn = document.getElementById('clearAsistencia');
    const form = document.getElementById('asistenciaForm');
    const payloadInput = document.getElementById('payload');
    const fechaInput = document.getElementById('fechaInput');
    const fechaPicker = document.getElementById('fechaAsistencia');

    if (!table || !perPageSelect) {
        return;
    }

    const currentPage = parseInt(table.dataset.page, 10) || 1;
    const currentPerPage = parseInt(table.dataset.perPage, 10) || 10;

    const storedPage = parseInt(sessionStorage.getItem('deportistas_page') || '0', 10);
    const storedPerPage = parseInt(sessionStorage.getItem('deportistas_per_page') || '0', 10);

    perPageSelect.value = String(currentPerPage);
    sessionStorage.setItem('deportistas_page', String(currentPage));
    sessionStorage.setItem('deportistas_per_page', String(currentPerPage));

    perPageSelect.addEventListener('change', function () {
        const size = parseInt(perPageSelect.value, 10) || currentPerPage;
        sessionStorage.setItem('deportistas_per_page', String(size));
        sessionStorage.setItem('deportistas_page', '1');
        const params = new URLSearchParams(window.location.search);
        params.set('url', 'registrar-asistencia');
        params.set('page', '1');
        params.set('per_page', String(size));
        window.location.href = 'index.php?' + params.toString();
    });

    function getToday() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function getCurrentFecha() {
        return (fechaPicker && fechaPicker.value) ? fechaPicker.value : getToday();
    }

    function attendanceStateKey(fecha, id) {
        return 'asistencia_estado_' + fecha + '_' + id;
    }

    function attendanceCommentKey(fecha, id) {
        return 'asistencia_comentario_' + fecha + '_' + id;
    }

    function existingValue(row, attrName) {
        return (row.getAttribute(attrName) || '').trim();
    }

    if (fechaPicker && fechaInput) {
        const initialFecha = fechaPicker.value || getToday();
        fechaPicker.value = initialFecha;
        fechaInput.value = initialFecha;
        sessionStorage.setItem('asistencia_fecha', initialFecha);

        fechaPicker.addEventListener('change', function () {
            const value = fechaPicker.value || getToday();
            const params = new URLSearchParams(window.location.search);
            params.set('url', 'registrar-asistencia');
            params.set('fecha', value);
            if (sessionStorage.getItem('deportistas_page')) {
                params.set('page', sessionStorage.getItem('deportistas_page'));
            }
            if (sessionStorage.getItem('deportistas_per_page')) {
                params.set('per_page', sessionStorage.getItem('deportistas_per_page'));
            }
            if (params.has('search') === false && document.querySelector('input[name="search"]')) {
                const search = document.querySelector('input[name="search"]').value.trim();
                if (search !== '') params.set('search', search);
            }
            if (params.has('categoria') === false && document.querySelector('select[name="categoria"]')) {
                const categoria = document.querySelector('select[name="categoria"]').value.trim();
                if (categoria !== '') params.set('categoria', categoria);
            }
            if (params.has('jornada') === false && document.querySelector('select[name="jornada"]')) {
                const jornada = document.querySelector('select[name="jornada"]').value.trim();
                if (jornada !== '') params.set('jornada', jornada);
            }
            window.location.href = 'index.php?' + params.toString();
        });
    }

    const rows = table.querySelectorAll('[data-id]');
    const currentFecha = getCurrentFecha();
    const selectedId = sessionStorage.getItem('deportistas_selected_id_' + currentFecha);
    if (selectedId) {
        rows.forEach((row) => {
            if (row.dataset.id === selectedId) {
                row.classList.add('table-primary');
            }
        });
    }

    rows.forEach((row) => {
        row.addEventListener('click', function () {
            rows.forEach((r) => r.classList.remove('table-primary'));
            row.classList.add('table-primary');
            sessionStorage.setItem('deportistas_selected_id_' + currentFecha, row.dataset.id || '');
        });
    });

    const pageLinks = document.querySelectorAll('.pagination a[data-page]');
    pageLinks.forEach((link) => {
        link.addEventListener('click', function () {
            const page = link.getAttribute('data-page');
            if (page) {
                sessionStorage.setItem('deportistas_page', page);
            }
        });
    });

    function getEstadoColor(estado) {
        switch (estado) {
            case 'Presente':
                return 'success';
            case 'Ausente':
                return 'danger';
            case 'Tarde':
                return 'warning';
            case 'Excusado':
                return 'secondary';
            default:
                return 'primary';
        }
    }

    function hasFecha() {
        return Boolean(getCurrentFecha());
    }

    function hasAnyMarked() {
        const fecha = getCurrentFecha();
        for (let i = 0; i < sessionStorage.length; i++) {
            const key = sessionStorage.key(i);
            if (key && key.startsWith('asistencia_estado_' + fecha + '_')) {
                const value = sessionStorage.getItem(key);
                if (value) {
                    return true;
                }
            }
        }
        return false;
    }

    function updateSubmitState() {
        if (!submitBtn) {
            return;
        }
        submitBtn.disabled = !hasAnyMarked() || !hasFecha();
    }

    function applyRowState(row) {
        const id = row.dataset.id || '';
        const fecha = getCurrentFecha();
        const storedEstado = sessionStorage.getItem(attendanceStateKey(fecha, id));
        const storedComentario = sessionStorage.getItem(attendanceCommentKey(fecha, id));
        const existingEstado = existingValue(row, 'data-existing-estado');
        const existingComentario = existingValue(row, 'data-existing-comentario');
        const estado = storedEstado || existingEstado;
        const comentario = storedComentario !== null ? storedComentario : existingComentario;

        if (!storedEstado && existingEstado) {
            sessionStorage.setItem(attendanceStateKey(fecha, id), existingEstado);
        }
        if (storedComentario === null && existingComentario !== '') {
            sessionStorage.setItem(attendanceCommentKey(fecha, id), existingComentario);
        }

        const buttons = row.querySelectorAll('.asistencia-btn');
        buttons.forEach((btn) => {
            const btnEstado = btn.getAttribute('data-estado');
            const color = getEstadoColor(btnEstado || '');
            btn.classList.remove('btn-success', 'btn-danger', 'btn-warning', 'btn-secondary', 'btn-primary');
            btn.classList.remove('btn-outline-success', 'btn-outline-danger', 'btn-outline-warning', 'btn-outline-secondary', 'btn-outline-primary');
            btn.classList.remove('btn-secondary');

            if (estado && btnEstado === estado) {
                btn.classList.add('btn-' + color);
            } else if (estado) {
                btn.classList.add('btn-outline-secondary');
            } else {
                btn.classList.add('btn-outline-' + color);
            }
        });

        const input = row.querySelector('.comentario-input');
        if (input) {
            input.value = comentario;
        }
    }

    rows.forEach((row) => {
        applyRowState(row);

        const buttons = row.querySelectorAll('.asistencia-btn');
        buttons.forEach((btn) => {
            btn.addEventListener('click', function () {
                const estado = btn.getAttribute('data-estado');
                if (!estado) {
                    return;
                }
                const id = row.dataset.id || '';
                sessionStorage.setItem(attendanceStateKey(currentFecha, id), estado);
                applyRowState(row);
                updateSubmitState();
            });
        });

        const input = row.querySelector('.comentario-input');
        if (input) {
            input.addEventListener('input', function () {
                const id = row.dataset.id || '';
                sessionStorage.setItem(attendanceCommentKey(currentFecha, id), input.value || '');
            });
        }
    });

    updateSubmitState();

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            const fecha = getCurrentFecha();
            const keysToRemove = [];
            for (let i = 0; i < sessionStorage.length; i++) {
                const key = sessionStorage.key(i);
                if (key && (key.startsWith('asistencia_estado_' + fecha + '_') || key.startsWith('asistencia_comentario_' + fecha + '_') || key === 'deportistas_selected_id_' + fecha)) {
                    keysToRemove.push(key);
                }
            }
            keysToRemove.forEach((key) => sessionStorage.removeItem(key));
            window.location.href = window.location.pathname + window.location.search;
        });
    }

    if (form && payloadInput && fechaInput) {
        form.addEventListener('submit', function (event) {
            if (!hasAnyMarked() || !hasFecha()) {
                event.preventDefault();
                return;
            }

            const data = [];
            const fechaActual = getCurrentFecha();
            for (let i = 0; i < sessionStorage.length; i++) {
                const key = sessionStorage.key(i);
                if (!key || !key.startsWith('asistencia_estado_' + fechaActual + '_')) {
                    continue;
                }
                const idStr = key.replace('asistencia_estado_' + fechaActual + '_', '');
                const id = parseInt(idStr, 10);
                if (!Number.isFinite(id) || id <= 0) {
                    continue;
                }
                const estado = sessionStorage.getItem(key) || '';
                if (!estado) {
                    continue;
                }
                const comentario = sessionStorage.getItem(attendanceCommentKey(fechaActual, id)) || '';
                data.push({
                    id_deportista: id,
                    estado: estado,
                    comentario: comentario,
                });
            }

            if (data.length === 0) {
                event.preventDefault();
                return;
            }

            const fechaFinal = fechaActual || getToday();
            sessionStorage.setItem('asistencia_fecha', fechaFinal);
            if (fechaPicker) {
                fechaPicker.value = fechaFinal;
            }
            payloadInput.value = JSON.stringify(data);
            fechaInput.value = fechaFinal;
        });
    }
})();
