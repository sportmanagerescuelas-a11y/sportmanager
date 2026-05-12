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

    if (fechaPicker && fechaInput) {
        const storedFecha = sessionStorage.getItem('asistencia_fecha');
        const initialFecha = storedFecha || getToday();
        fechaPicker.value = initialFecha;
        fechaInput.value = initialFecha;
        sessionStorage.setItem('asistencia_fecha', initialFecha);

        fechaPicker.addEventListener('change', function () {
            const value = fechaPicker.value || getToday();
            sessionStorage.setItem('asistencia_fecha', value);
            fechaInput.value = value;
            updateSubmitState();
        });
    }

    const rows = table.querySelectorAll('tbody tr[data-id]');
    const selectedId = sessionStorage.getItem('deportistas_selected_id');
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
            sessionStorage.setItem('deportistas_selected_id', row.dataset.id || '');
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
        return Boolean(sessionStorage.getItem('asistencia_fecha'));
    }

    function hasAnyMarked() {
        for (let i = 0; i < sessionStorage.length; i++) {
            const key = sessionStorage.key(i);
            if (key && key.startsWith('asistencia_estado_')) {
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
        const estado = sessionStorage.getItem('asistencia_estado_' + id);
        const comentario = sessionStorage.getItem('asistencia_comentario_' + id) || '';

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
                sessionStorage.setItem('asistencia_estado_' + id, estado);
                applyRowState(row);
                updateSubmitState();
            });
        });

        const input = row.querySelector('.comentario-input');
        if (input) {
            input.addEventListener('input', function () {
                const id = row.dataset.id || '';
                sessionStorage.setItem('asistencia_comentario_' + id, input.value || '');
            });
        }
    });

    updateSubmitState();

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            sessionStorage.clear();
            window.location.href = 'index.php?url=registrar-asistencia';
        });
    }

    if (form && payloadInput && fechaInput) {
        form.addEventListener('submit', function (event) {
            if (!hasAnyMarked() || !hasFecha()) {
                event.preventDefault();
                return;
            }

            const data = [];
            for (let i = 0; i < sessionStorage.length; i++) {
                const key = sessionStorage.key(i);
                if (!key || !key.startsWith('asistencia_estado_')) {
                    continue;
                }
                const idStr = key.replace('asistencia_estado_', '');
                const id = parseInt(idStr, 10);
                if (!Number.isFinite(id) || id <= 0) {
                    continue;
                }
                const estado = sessionStorage.getItem(key) || '';
                if (!estado) {
                    continue;
                }
                const comentario = sessionStorage.getItem('asistencia_comentario_' + id) || '';
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

            const fechaFinal = sessionStorage.getItem('asistencia_fecha') || getToday();
            sessionStorage.setItem('asistencia_fecha', fechaFinal);
            if (fechaPicker) {
                fechaPicker.value = fechaFinal;
            }
            payloadInput.value = JSON.stringify(data);
            fechaInput.value = fechaFinal;
        });
    }
})();
