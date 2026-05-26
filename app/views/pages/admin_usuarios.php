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
<div class="container mt-5">
    <?php if (!$isSchoolAdminView): ?>
    <h2 class="text-center mb-4">Usuarios Pendientes</h2>
    <table class="table table-bordered text-center">
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Escuela</th><th>Rol solicitado</th><th>Pago admin</th><th>Acciones</th></tr>
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
                        <?php if ((int)$user['id_rol'] === 3): ?>
                            <span class="badge <?= ($user['estado_pago_admin'] ?? '') === 'verificado' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                <?= htmlspecialchars((string)($user['estado_pago_admin'] ?? 'pendiente')) ?>
                            </span>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int)$user['id_rol'] === 3 && ($user['estado_pago_admin'] ?? '') !== 'verificado'): ?>
                            <button
                                type="button"
                                class="btn btn-warning btn-sm btn-open-verify-modal"
                                data-id-usuario="<?= htmlspecialchars((string)$user['id_usuario']) ?>"
                                data-comprobante-path="<?= htmlspecialchars((string)($user['comprobante_path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            >
                                Verificar pago
                            </button>
                        <?php endif; ?>
                        <?php if ((int)$user['id_rol'] === 3 && ($user['estado_pago_admin'] ?? '') !== 'verificado'): ?>
                            <button type="button" class="btn btn-success btn-sm" disabled title="Primero debes verificar el pago.">Aprobar</button>
                        <?php else: ?>
                            <form action="controllers/adminUsuarioController.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string)$user['id_usuario']) ?>">
                                <button name="aprobar" class="btn btn-success btn-sm">Aprobar</button>
                            </form>
                        <?php endif; ?>
                        <form action="controllers/adminUsuarioController.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string)$user['id_usuario']) ?>">
                            <button name="rechazar" class="btn btn-danger btn-sm">Rechazar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <hr class="mt-5 mb-4">
    <h2 class="text-center mb-4"><?= $isSchoolAdminView ? 'Usuarios Registrados En Tu Escuela' : 'Usuarios Activos y Deshabilitados' ?></h2>
    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Escuela</th><th>Rol</th><th>Deportistas</th><th>Estado</th><th>Acciones</th></tr>
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
                    <td>
                        <a href="editar_usuario&id=<?= urlencode((string)$user['id_usuario']) ?>" class="btn btn-primary btn-sm">Editar</a>
                        <a href="ver_deportistas_usuario&id=<?= urlencode((string)$user['id_usuario']) ?>" class="btn btn-info btn-sm">Ver Deportistas</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
                <div id="verifyImageWrap" class="text-center d-none">
                    <img id="verifyImagePreview" src="" alt="Comprobante de pago" class="img-fluid rounded border" style="max-height: 70vh;">
                </div>
                <div id="verifyPdfWrap" class="d-none">
                    <iframe id="verifyPdfPreview" src="" title="Comprobante PDF" class="w-100 border rounded" style="height: 70vh;"></iframe>
                </div>
                <p id="verifyImageFallback" class="text-muted mb-0 d-none">No hay imagen de comprobante disponible para este usuario.</p>
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
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('verifyPaymentModal');
    if (!modalElement || !window.bootstrap || !bootstrap.Modal) return;

    const modal = new bootstrap.Modal(modalElement);
    const userIdInput = document.getElementById('verifyPaymentUserId');
    const image = document.getElementById('verifyImagePreview');
    const imageWrap = document.getElementById('verifyImageWrap');
    const pdf = document.getElementById('verifyPdfPreview');
    const pdfWrap = document.getElementById('verifyPdfWrap');
    const fallback = document.getElementById('verifyImageFallback');
    const verifyForm = document.getElementById('verifyPaymentForm');
    const verifyButton = verifyForm.querySelector('button[name="verificar_pago"]');

    document.querySelectorAll('.btn-open-verify-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const userId = btn.getAttribute('data-id-usuario') || '';
            const comprobantePath = btn.getAttribute('data-comprobante-path') || '';
            const lowerPath = comprobantePath.toLowerCase();
            const isPdf = lowerPath.endsWith('.pdf');
            const isImage = /\.(jpg|jpeg|png|webp|gif|bmp)$/i.test(comprobantePath);

            userIdInput.value = userId;
            if (comprobantePath !== '' && isPdf) {
                pdf.src = comprobantePath;
                pdfWrap.classList.remove('d-none');
                image.removeAttribute('src');
                imageWrap.classList.add('d-none');
                fallback.classList.add('d-none');
                verifyButton.disabled = false;
            } else if (comprobantePath !== '' && isImage) {
                image.src = comprobantePath;
                imageWrap.classList.remove('d-none');
                pdf.removeAttribute('src');
                pdfWrap.classList.add('d-none');
                fallback.classList.add('d-none');
                verifyButton.disabled = false;
            } else {
                image.removeAttribute('src');
                imageWrap.classList.add('d-none');
                pdf.removeAttribute('src');
                pdfWrap.classList.add('d-none');
                fallback.classList.remove('d-none');
                verifyButton.disabled = true;
            }

            modal.show();
        });
    });
});
</script>
<?php endif; ?>
