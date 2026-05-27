<?php
$viewData = get_defined_vars();
$events = is_array($viewData['events'] ?? null) ? $viewData['events'] : [];
$athletes = is_array($viewData['athletes'] ?? null) ? $viewData['athletes'] : [];
$rol = (int)($viewData['rol'] ?? ($_SESSION['rol'] ?? 0));
$rolLabel = (string)($_SESSION['nombre_rol'] ?? '');
if ($rolLabel === '') {
    $rolLabel = [1 => 'Acudiente', 2 => 'Entrenador', 3 => 'Administrador', 4 => 'Superadmin'][$rol] ?? 'Usuario';
}

$dashboardShieldPath = 'assets/img/balonfutbol.png';
if ($rol !== 4 && isset($_SESSION['usuario']['id_escuela']) && (int)$_SESSION['usuario']['id_escuela'] > 0) {
    try {
        require_once __DIR__ . '/../../../config/conexion.php';
        if (isset($conexion) && $conexion instanceof PDO) {
            $shieldStmt = $conexion->prepare('SELECT escudo_path FROM escuelas WHERE id_escuela = ? LIMIT 1');
            $shieldStmt->execute([(int)$_SESSION['usuario']['id_escuela']]);
            $shield = trim((string)$shieldStmt->fetchColumn());
            if ($shield !== '') {
                $dashboardShieldPath = $shield;
            }
        }
    } catch (Throwable) {
    }
}
?>
<div class="dashboard position-relative">
    <div class="dashboard-shield-watermark" style="background-image: url('<?= htmlspecialchars(str_replace("'", '%27', str_replace('\\', '/', $dashboardShieldPath)), ENT_QUOTES, 'UTF-8') ?>');"></div>
    <div class="rol-box"><?= htmlspecialchars($rolLabel) ?></div>

    <?php if (count($events) > 0): ?>
        <div class="alert alert-success alert-fijo" id="eventosAlert">
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
                                    data-bs-dismiss="modal"
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
                        <a href="iniciar&id_evento=<?= (int)$e->id_evento ?>&evento=<?= urlencode((string)$e->titulo) ?>&monto=<?= urlencode((string)$e->costo) ?>&cantidad=1" class="btn btn-warning">Pagar</a>
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
    <br>

    <div class="opciones">
        <?php if ($rol === 4): ?>
            <a href="admin_usuarios" class="card-dashboard">Validar Pagos Admin</a>
            <a href="gestion_escuelas" class="card-dashboard">Gestionar Escuelas</a>
        <?php elseif ($rol === 3): ?>
            <a href="admin_usuarios" class="card-dashboard">Gestionar Usuarios</a>
            <a href="deportistas" class="card-dashboard">Gestionar Deportistas</a>
            <a href="index.php?action=listar" class="card-dashboard">Reporte de Pago</a>
            <a href="productos" class="card-dashboard">Productos</a>
            <a href="reportes" class="card-dashboard">Reportes Generales</a>
            <a href="gestion_eventos" class="card-dashboard">Eventos</a>
            <a href="uniformes" class="card-dashboard">Uniformes</a>
        <?php elseif ($rol === 2): ?>
            <a href="deportistas" class="card-dashboard">Ver Deportistas</a>
            <a href="registrar-asistencia" class="card-dashboard">Registrar Asistencia</a>
            <a href="reportes" class="card-dashboard">Reportes</a>
            <a href="uniformes" class="card-dashboard">Uniformes</a>
        <?php else: ?>
            <a href="deportistas" class="card-dashboard">Registrar Deportista</a>
            <a href="asistencia-hijos" class="card-dashboard">Ver Asistencias</a>
            <a href="pagos" class="card-dashboard">Mis Pagos</a>
            <a href="uniformes" class="card-dashboard">Uniformes</a>
        <?php endif; ?>
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
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
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
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            });
        });
    });
</script>
