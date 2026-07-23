<?php
$viewData = get_defined_vars();
$usuariosPendientes = is_array($viewData['usuariosPendientes'] ?? null) ? $viewData['usuariosPendientes'] : [];
$usuariosAprobados = is_array($viewData['usuariosAprobados'] ?? null) ? $viewData['usuariosAprobados'] : [];
$isSchoolAdminView = (bool)($viewData['isSchoolAdminView'] ?? false);

if (!function_exists('rol_nombre')) {
function rol_nombre(int $rol): string
{
    return [1 => 'Acudiente', 2 => 'Entrenador', 3 => 'Administrador', 4 => 'Superadmin'][$rol] ?? 'Desconocido';
}
}
?>
<br>
<br>
<div class="container-fluid admin-users-page school-style-page mt-5">
    <?php if (!$isSchoolAdminView || !empty($usuariosPendientes)): ?>
    <h2 class="text-center mb-4">Usuarios Pendientes</h2>
    <div class="table-responsive admin-users-table-wrap">
        <table class="table table-bordered text-center admin-users-table">
            <thead>
                <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Escuela</th><th>Rol solicitado</th><th>Pago admin</th><th class="admin-users-actions-head">Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($usuariosPendientes as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$user['id_usuario']) ?></td>
                        <td><?= htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']) ?></td>
                        <td><?= htmlspecialchars((string)$user['email']) ?></td>
                        <td><?= htmlspecialchars((string)($user['nombre_escuela'] ?? 'Sin escuela')) ?></td>
                        <td><?= htmlspecialchars(rol_nombre((int)$user['id_rol'])) ?></td>
                        <td>
                            <?php if ($isSchoolAdminView): ?>
                                <span class="badge bg-secondary">Pendiente de aprobación</span>
                            <?php elseif ((int)$user['id_rol'] === 3): ?>
                                <span class="badge <?= ($user['estado'] ?? '') === 'pago_pendiente' ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                                    <?= htmlspecialchars((string)($user['estado'] ?? 'pendiente')) ?>
                                </span>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td class="admin-users-actions-cell">
                            <div class="admin-users-actions">
                                <?php if ($isSchoolAdminView): ?>
                                    <form action="controllers/editarUsuarioController.php" method="POST" class="m-0">
                                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string)$user['id_usuario']) ?>">
                                        <input type="hidden" name="accion" value="activar">
                                        <button class="btn btn-success btn-sm">Aprobar</button>
                                    </form>
                                    <form action="controllers/editarUsuarioController.php" method="POST" class="m-0">
                                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string)$user['id_usuario']) ?>">
                                        <input type="hidden" name="accion" value="deshabilitar">
                                        <button class="btn btn-danger btn-sm">Rechazar</button>
                                    </form>
                                <?php elseif ((int)$user['id_rol'] === 3 && ($user['estado'] ?? '') === 'pago_pendiente'): ?>
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm btn-open-verify-modal"
                                        data-id-usuario="<?= htmlspecialchars((string)$user['id_usuario']) ?>"
                                        data-user-name="<?= htmlspecialchars((string)($user['nombres'] . ' ' . $user['apellidos']), ENT_QUOTES, 'UTF-8') ?>"
                                        data-factura-id="<?= htmlspecialchars((string)($user['factura_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-factura-numero="<?= htmlspecialchars((string)($user['factura_numero'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-factura-fecha="<?= htmlspecialchars((string)($user['factura_fecha'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-factura-monto="<?= htmlspecialchars((string)($user['factura_monto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-factura-descripcion="<?= htmlspecialchars((string)($user['factura_descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-factura-tipo-pago="<?= htmlspecialchars((string)($user['factura_tipo_pago'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        Ver comprobante
                                    </button>
                                <?php endif; ?>

                                <?php if (!empty($user['factura_id'])): ?>
                                    <a href="index.php?action=ver&id=<?= urlencode((string)$user['factura_id']) ?>" class="btn btn-info btn-sm">Ver factura</a>
                                <?php endif; ?>

                                <?php if (!$isSchoolAdminView): ?>
                                    <?php if ((int)$user['id_rol'] === 3 && ($user['estado'] ?? '') === 'pago_pendiente'): ?>
                                        <button type="button" class="btn btn-success btn-sm" disabled title="Primero debes verificar el pago.">Aprobar</button>
                                    <?php else: ?>
                                        <form action="controllers/adminUsuarioController.php" method="POST" class="m-0">
                                            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string)$user['id_usuario']) ?>">
                                            <button name="aprobar" class="btn btn-success btn-sm">Aprobar</button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="controllers/adminUsuarioController.php" method="POST" class="m-0">
                                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string)$user['id_usuario']) ?>">
                                        <button name="rechazar" class="btn btn-danger btn-sm">Rechazar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <hr class="mt-5 mb-4">
    <h2 class="text-center mb-4"><?= $isSchoolAdminView ? 'Usuarios Registrados En Tu Escuela' : 'Usuarios Activos y Deshabilitados' ?></h2>
    <div class="table-responsive admin-users-table-wrap">
        <table class="table table-bordered text-center admin-users-table">
            <thead class="table-dark">
                <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Escuela</th><th>Rol</th><th>Deportistas</th><th>Estado</th><th class="admin-users-actions-head">Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($usuariosAprobados as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$user['id_usuario']) ?></td>
                        <td><?= htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']) ?></td>
                        <td><?= htmlspecialchars((string)$user['email']) ?></td>
                        <td><?= htmlspecialchars((string)($user['nombre_escuela'] ?? 'Sin escuela')) ?></td>
                        <td><?= htmlspecialchars(rol_nombre((int)$user['id_rol'])) ?></td>
                        <td><?= (int)$user['total_deportistas'] ?></td>
                        <td><?= htmlspecialchars((string)$user['estado']) ?></td>
                        <td class="admin-users-actions-cell">
                            <div class="admin-users-actions">
                                <a href="editar_usuario&id=<?= urlencode((string)$user['id_usuario']) ?>" class="btn btn-primary btn-sm">Editar</a>
                                <a href="ver_deportistas_usuario&id=<?= urlencode((string)$user['id_usuario']) ?>" class="btn btn-info btn-sm">Ver Deportistas</a>
                                <?php if (!empty($user['factura_id'])): ?>
                                    <a href="index.php?action=ver&id=<?= urlencode((string)$user['factura_id']) ?>" class="btn btn-warning btn-sm">Ver factura</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="dashboard" class="btn btn-primary mt-3">Volver</a>
</div>
<br>

<?php if (!$isSchoolAdminView): ?>
<div class="modal fade" id="verifyPaymentModal" tabindex="-1" aria-labelledby="verifyPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verifyPaymentModalLabel">Verificar comprobante de pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="verifyInvoiceWrap" class="d-none">
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <h5 class="mb-1">Factura de pago</h5>
                                <div class="small text-muted" id="verifyInvoiceDate"></div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success" id="verifyInvoiceType"></span>
                                <div class="fw-semibold mt-2">$<span id="verifyInvoiceMonto"></span></div>
                            </div>
                        </div>
                        <hr>
                        <div><strong>Factura:</strong> <span id="verifyInvoiceNumber"></span></div>
                        <div><strong>Usuario:</strong> <span id="verifyInvoiceUserName"></span></div>
                        <div><strong>Concepto:</strong> <span id="verifyInvoiceDescripcion"></span></div>
                    </div>
                </div>
                <p id="verifyInvoiceFallback" class="text-muted mb-0 d-none">No hay factura disponible para este usuario.</p>
                <div id="verifyInvoiceActions" class="mt-3 d-none">
                    <a href="#" id="verifyInvoiceLink" class="btn btn-outline-primary">Abrir factura completa</a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="verifyPaymentForm" action="controllers/adminUsuarioController.php" method="POST" class="m-0">
                    <input type="hidden" name="id_usuario" id="verifyPaymentUserId" value="">
                    <button type="submit" name="verificar_pago" class="btn btn-warning">Aceptar y verificar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    const modalElement = document.getElementById('verifyPaymentModal');
    if (!modalElement || !window.bootstrap || !bootstrap.Modal) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const userIdInput = document.getElementById('verifyPaymentUserId');
    const invoiceWrap = document.getElementById('verifyInvoiceWrap');
    const invoiceNumber = document.getElementById('verifyInvoiceNumber');
    const invoiceDate = document.getElementById('verifyInvoiceDate');
    const invoiceUserName = document.getElementById('verifyInvoiceUserName');
    const invoiceDescripcion = document.getElementById('verifyInvoiceDescripcion');
    const invoiceMonto = document.getElementById('verifyInvoiceMonto');
    const invoiceType = document.getElementById('verifyInvoiceType');
    const invoiceLink = document.getElementById('verifyInvoiceLink');
    const invoiceActions = document.getElementById('verifyInvoiceActions');
    const invoiceFallback = document.getElementById('verifyInvoiceFallback');
    const verifyForm = document.getElementById('verifyPaymentForm');
    const verifyButton = verifyForm.querySelector('button[name="verificar_pago"]');

    document.querySelectorAll('.btn-open-verify-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const userId = btn.getAttribute('data-id-usuario') || '';
            const userName = btn.getAttribute('data-user-name') || '';
            const facturaId = btn.getAttribute('data-factura-id') || '';
            const facturaNumero = btn.getAttribute('data-factura-numero') || '';
            const facturaFecha = btn.getAttribute('data-factura-fecha') || '';
            const facturaMonto = btn.getAttribute('data-factura-monto') || '';
            const facturaDescripcion = btn.getAttribute('data-factura-descripcion') || '';
            const facturaTipoPago = btn.getAttribute('data-factura-tipo-pago') || '';

            userIdInput.value = userId;
            invoiceNumber.textContent = facturaNumero !== '' ? facturaNumero : 'N/A';
            invoiceDate.textContent = facturaFecha !== '' ? facturaFecha : '';
            invoiceUserName.textContent = userName;
            invoiceDescripcion.textContent = facturaDescripcion !== '' ? facturaDescripcion : 'Sin descripcion';
            invoiceMonto.textContent = facturaMonto !== '' ? parseFloat(facturaMonto).toFixed(2) : '0.00';
            invoiceType.textContent = facturaTipoPago !== '' ? facturaTipoPago.toUpperCase() : 'PAGO';
            invoiceLink.href = facturaId !== '' ? 'index.php?action=ver&id=' + encodeURIComponent(facturaId) : '#';

            if (facturaId !== '') {
                invoiceWrap.classList.remove('d-none');
                invoiceActions.classList.remove('d-none');
                invoiceFallback.classList.add('d-none');
                verifyButton.disabled = false;
            } else {
                invoiceWrap.classList.add('d-none');
                invoiceActions.classList.add('d-none');
                invoiceFallback.classList.remove('d-none');
                verifyButton.disabled = true;
            }

            modal.show();
        });
    });
});
</script>
<?php endif; ?>
