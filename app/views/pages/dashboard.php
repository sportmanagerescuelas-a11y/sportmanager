<?php
$viewData = get_defined_vars();
$events = is_array($viewData['events'] ?? null) ? $viewData['events'] : [];
$athletes = is_array($viewData['athletes'] ?? null) ? $viewData['athletes'] : [];
$rol = (int)($viewData['rol'] ?? ($_SESSION['rol'] ?? 0));
$rolLabel = (string)($_SESSION['nombre_rol'] ?? '');
if ($rolLabel === '') {
    $rolLabel = [1 => 'Acudiente', 2 => 'Entrenador', 3 => 'Administrador', 4 => 'Superadmin'][$rol] ?? 'Usuario';
}
$dashboardShieldPath = isset($schoolShieldPath) && is_string($schoolShieldPath) && trim($schoolShieldPath) !== ''
    ? trim($schoolShieldPath)
    : '/sportmanager/assets/img/balonfutbol.png';
$dashboardShieldPath = str_replace('\\', '/', $dashboardShieldPath);
$dashboardShieldPath = str_replace(['"', "'", ' '], ['%22', '%27', '%20'], $dashboardShieldPath);

$dashboardActions = [];
if ($rol === 4) {
    $dashboardActions = [
        ['href' => 'admin_usuarios', 'label' => 'Validar Pagos Admin'],
        ['href' => 'gestion_escuelas', 'label' => 'Gestionar Escuelas'],
    ];
} elseif ($rol === 3) {
    $dashboardActions = [
        ['href' => 'admin_usuarios', 'label' => 'Gestionar Usuarios'],
        ['href' => 'deportistas', 'label' => 'Gestionar Deportistas'],
        ['href' => 'index.php?action=listar', 'label' => 'Reporte de Pago'],
        ['href' => 'productos', 'label' => 'Productos'],
        ['href' => 'reportes', 'label' => 'Reportes Generales'],
        ['href' => 'gestion_eventos', 'label' => 'Eventos'],
        ['href' => 'uniformes', 'label' => 'Uniformes'],
    ];
} elseif ($rol === 2) {
    $dashboardActions = [
        ['href' => 'deportistas', 'label' => 'Ver Deportistas'],
        ['href' => 'registrar-asistencia', 'label' => 'Registrar Asistencia'],
        ['href' => 'reportes', 'label' => 'Reportes'],
        ['href' => 'uniformes', 'label' => 'Uniformes'],
    ];
} else {
    $dashboardActions = [
        ['href' => 'deportistas', 'label' => 'Registrar Deportista'],
        ['href' => 'asistencia-hijos', 'label' => 'Ver Asistencias'],
        ['href' => 'pagos', 'label' => 'Mis Pagos'],
        ['href' => 'uniformes', 'label' => 'Uniformes'],
    ];
}

$dashboardActionChunks = array_chunk($dashboardActions, $rol === 3 ? 3 : 2);
?>
<style>
    main { padding-bottom: 0 !important; }
    footer.site-footer { margin-top: 0 !important; }
</style>
<div class="dashboard position-relative">
    <div class="dashboard-shield-watermark" style="background-image: url(<?= htmlspecialchars($dashboardShieldPath, ENT_QUOTES, 'UTF-8') ?>);"></div>
    <?php if (count($events) > 0): ?>
        <div class="alert alert-success" id="eventosAlert">
            <span>Tienes <?= count($events) ?> evento(s) disponible(s)</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#seleccionarEventoModal">Ver</button>
        </div>

        <div class="modal fade" id="seleccionarEventoModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border: 2px solid var(--school-primary-color); background-color: #fff;">
                    <div class="modal-header" style="background-color: #fff; border-bottom: 1px solid var(--school-primary-color);">
                        <h5 class="modal-title w-100 text-center">Selecciona un evento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="background-color: #fff;">
                        <div class="d-grid gap-2">
                            <?php foreach ($events as $e): ?>
                                <button
                                    type="button"
                                    class="btn btnSeleccionarEvento"
                                    style="border-color: var(--school-primary-color); color: var(--school-primary-color);"
                                    data-evento-id="<?= (int)$e->id_evento ?>"
                                >
                                    <?= htmlspecialchars((string)$e->titulo) ?> - <?= htmlspecialchars((string)$e->fecha) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer" style="background-color: #fff; border-top: 1px solid var(--school-primary-color);">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="dashboard-role-banner">
        <span class="dashboard-role-banner__tag">ROL</span>
        <span class="dashboard-role-banner__value"><?= htmlspecialchars($rolLabel, ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <!-- <div class="dashboard-search">
        <label for="dashboardSearch" class="dashboard-search__label">Buscar accesos</label>
        <input type="search" id="dashboardSearch" class="form-control dashboard-search__input" placeholder="Escribe para filtrar opciones">
    </div> -->

    <?php foreach ($events as $e): ?>
        <?php
        $registeredAthleteIds = array_map('strval', is_array($e->user_registered_athlete_ids ?? null) ? $e->user_registered_athlete_ids : []);
        $availableAthletes = array_values(array_filter($athletes, static function ($athlete) use ($registeredAthleteIds): bool {
            $athleteId = (string)($athlete->id_deportista ?? '');
            return $athleteId !== '' && !in_array($athleteId, $registeredAthleteIds, true);
        }));
        $availableAthletesCount = count($availableAthletes);
        ?>
        <div class="modal fade" id="evento<?= (int)$e->id_evento ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border: 2px solid var(--school-primary-color); background-color: #fff;">
                    <div class="modal-header" style="background-color: #fff; border-bottom: 1px solid var(--school-primary-color);">
                        <h5 class="modal-title w-100 text-center"><?= htmlspecialchars((string)$e->titulo) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="background-color: #fff;">
                        <p><b>Fecha:</b> <?= htmlspecialchars((string)$e->fecha) ?></p>
                        <p><b>Tipo:</b> <?= htmlspecialchars((string)$e->tipo_evento) ?></p>
                        <p><b>Costo:</b> $<?= htmlspecialchars((string)$e->costo) ?></p>
                    </div>
                    <div class="modal-footer" style="background-color: #fff; border-top: 1px solid var(--school-primary-color);">
                        <span class="me-auto">Inscritos: <b id="count<?= (int)$e->id_evento ?>"><?= (int)$e->total_inscritos ?></b></span>
                        <?php if ((float)$e->costo > 0 && count($registeredAthleteIds) > 0): ?>
                            <a href="pago_evento&id_evento=<?= (int)$e->id_evento ?>" class="btn btn-warning">Pagar</a>
                        <?php elseif ((float)$e->costo <= 0): ?>
                            <span class="badge text-bg-secondary">Evento gratuito</span>
                        <?php endif; ?>
                        <?php if ($e->inscrito): ?>
                            <button class="btn btn-success" disabled>Ya inscrito</button>
                        <?php else: ?>
                            <button class="btn btn-primary btnInscribirseEvento" data-evento="<?= (int)$e->id_evento ?>" data-athletes-count="<?= $availableAthletesCount ?>">Inscribirse</button>
                        <?php endif; ?>
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="seleccionarDeportista<?= (int)$e->id_evento ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Seleccionar Deportista</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($availableAthletesCount > 0): ?>
                            <select class="form-control selectDeportista" data-evento="<?= (int)$e->id_evento ?>">
                                <option value="">Seleccione...</option>
                                <?php foreach ($availableAthletes as $d): ?>
                                    <option value="<?= htmlspecialchars((string)$d->id_deportista, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($d->nombres . ' ' . $d->apellidos) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <p>Todos tus deportistas ya estan inscritos en este evento.</p>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success btnConfirmarInscripcion" data-evento="<?= (int)$e->id_evento ?>">Confirmar</button>
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="subtitulo">
        <h2>BIENVENID@, <?= htmlspecialchars(($_SESSION['usuario']['nombres'] ?? '') . ' ' . ($_SESSION['usuario']['apellidos'] ?? '')) ?></h2>
    </div>

    <div class="opciones opciones--dashboard">
        <?php foreach ($dashboardActionChunks as $group): ?>
            <div class="dashboard-group">
                <?php foreach ($group as $action): ?>
                    <a href="<?= htmlspecialchars($action['href'], ENT_QUOTES, 'UTF-8') ?>" class="card-dashboard" data-dashboard-action="1">
                        <?= htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        function normalizarIdNumerico(value) {
            const text = String(value ?? "").trim();
            if (text === "") return "";
            const n = parseInt(text, 10);
            if (!Number.isFinite(n) || n < 0) return "";
            return String(n);
        }

        function extraerIdEventoDesdeElemento(element) {
            if (!element) return "";

            const byDataset = normalizarIdNumerico(element.dataset ? element.dataset.evento : "");
            if (byDataset !== "") return byDataset;

            const byAttr = normalizarIdNumerico(element.getAttribute ? element.getAttribute("data-evento") : "");
            if (byAttr !== "") return byAttr;

            const modal = element.closest ? element.closest("[id^='seleccionarDeportista'], [id^='evento']") : null;
            if (modal && modal.id) {
                const match = modal.id.match(/(\d+)$/);
                if (match && match[1]) return normalizarIdNumerico(match[1]);
            }

            return "";
        }

        function obtenerIdDeportistaDesdeSelect(select) {
            if (!select) return "";

            let value = (select.value || "").trim();
            if (value !== "") return value;

            const seleccionada = select.options[select.selectedIndex];
            if (seleccionada && (seleccionada.value || "").trim() !== "") {
                return seleccionada.value.trim();
            }

            const opcionesValidas = Array.from(select.options).filter(opt => (opt.value || "").trim() !== "");
            if (opcionesValidas.length === 1) {
                select.value = opcionesValidas[0].value;
                return opcionesValidas[0].value.trim();
            }

            return "";
        }

        function showModalAfterCurrentCloses(targetModal, sourceElement) {
            if (!targetModal || !window.bootstrap || !bootstrap.Modal) {
                return;
            }

            const showTarget = function () {
                bootstrap.Modal.getOrCreateInstance(targetModal).show();
            };
            const currentModal = sourceElement && sourceElement.closest
                ? sourceElement.closest('.modal.show')
                : null;

            if (!currentModal || currentModal === targetModal) {
                showTarget();
                return;
            }

            currentModal.addEventListener('hidden.bs.modal', showTarget, { once: true });
            bootstrap.Modal.getOrCreateInstance(currentModal).hide();
        }

        function registrarInscripcion(id_evento, id_deportista) {
            const idEventoSeguro = normalizarIdNumerico(id_evento);
            if (idEventoSeguro === "" || idEventoSeguro === "0") {
                alert("No se pudo identificar el evento. Cierra y vuelve a abrir la ventana del evento.");
                return;
            }

            const payload = new URLSearchParams();
            payload.set("id_evento", idEventoSeguro);
            payload.set("id_deportista", String(id_deportista || "").trim());

            fetch("inscribirse", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: payload.toString()
            }).then(res => res.text()).then(data => {
                if (data === "ok") {
                    location.reload();
                } else {
                    alert(data);
                }
            });
        }

        document.querySelectorAll(".btnInscribirseEvento").forEach(btn => {
            btn.addEventListener("click", function() {
                const id_evento = extraerIdEventoDesdeElemento(this);
                const athletesCount = parseInt(this.dataset.athletesCount || "0", 10);
                const select = document.querySelector(`.selectDeportista[data-evento='${id_evento}']`);

                if (id_evento === "" || id_evento === "0") {
                    alert("No se pudo identificar el evento seleccionado.");
                    return;
                }

                if (athletesCount <= 0 || !select) {
                    alert("No tienes deportistas registrados");
                    return;
                }

                if (athletesCount === 1) {
                    const unicaOpcion = Array.from(select.options).find(opt => opt.value !== "");
                    if (!unicaOpcion) {
                        alert("No tienes deportistas disponibles");
                        return;
                    }
                    registrarInscripcion(id_evento, unicaOpcion.value);
                    return;
                }

                const modalEl = document.getElementById(`seleccionarDeportista${id_evento}`);
                if (modalEl) {
                    const selectModal = modalEl.querySelector(".selectDeportista");
                    if (selectModal && !selectModal.value) {
                        const primeraValida = Array.from(selectModal.options).find(opt => (opt.value || "").trim() !== "");
                        if (primeraValida) {
                            selectModal.value = primeraValida.value;
                        }
                    }
                    showModalAfterCurrentCloses(modalEl, this);
                }
            });
        });

        document.querySelectorAll(".btnConfirmarInscripcion").forEach(btn => {
            btn.addEventListener("click", function() {
                const id_evento = extraerIdEventoDesdeElemento(this);
                if (id_evento === "" || id_evento === "0") {
                    alert("No se pudo identificar el evento seleccionado.");
                    return;
                }
                const modalEl = document.getElementById(`seleccionarDeportista${id_evento}`);
                const select = modalEl ? modalEl.querySelector(".selectDeportista") : document.querySelector(`.selectDeportista[data-evento='${id_evento}']`);
                const id_deportista = obtenerIdDeportistaDesdeSelect(select);
                if (!id_deportista) {
                    alert("Selecciona un deportista");
                    return;
                }
                registrarInscripcion(id_evento, id_deportista);
            });
        });

        document.querySelectorAll(".btnSeleccionarEvento").forEach(btn => {
            btn.addEventListener("click", function() {
                const eventId = normalizarIdNumerico(this.dataset.eventoId || "");
                if (eventId === "" || eventId === "0") {
                    alert("No se pudo identificar el evento seleccionado.");
                    return;
                }
                const modalEl = document.getElementById(`evento${eventId}`);
                if (!modalEl) {
                    alert("No se encontro la informacion del evento.");
                    return;
                }
                showModalAfterCurrentCloses(modalEl, this);
            });
        });

        const dashboardSearch = document.getElementById("dashboardSearch");
        const dashboardCards = Array.from(document.querySelectorAll(".card-dashboard[data-dashboard-action='1']"));
        const dashboardGroups = Array.from(document.querySelectorAll(".dashboard-group"));

        function syncDashboardGroups() {
            dashboardGroups.forEach((group) => {
                const visibleCards = Array.from(group.querySelectorAll(".card-dashboard[data-dashboard-action='1']")).filter((card) => card.style.display !== "none");
                group.style.display = visibleCards.length > 0 ? "" : "none";
            });
        }

        if (dashboardSearch && dashboardCards.length > 0) {
            dashboardSearch.addEventListener("input", function () {
                const term = dashboardSearch.value.trim().toLowerCase();
                dashboardCards.forEach((card) => {
                    const label = (card.textContent || "").trim().toLowerCase();
                    card.style.display = term === "" || label.includes(term) ? "" : "none";
                });
                syncDashboardGroups();
            });
        }
    });
</script>
