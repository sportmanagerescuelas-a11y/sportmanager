<?php
$viewData = get_defined_vars();
$data = is_object($viewData['athlete'] ?? null) ? $viewData['athlete'] : (object)[
    'foto' => 'default.png',
    'nombres' => '',
    'apellidos' => '',
    'tipo_documento' => 'CC',
    'id_deportista' => '',
    'fecha_nacimiento' => '',
    'genero' => 'Masculino',
    'jornada' => 'Manana',
    'id_categoria' => 0,
    'id_nivel' => 0,
];
$categorias = is_array($viewData['categorias'] ?? null) ? $viewData['categorias'] : [];
$niveles = is_array($viewData['niveles'] ?? null) ? $viewData['niveles'] : [];
$fotoActual = !empty($data->foto) ? 'fotos/' . ltrim((string)$data->foto, '/\\') : 'fotos/default.png';
?>

<div class="container py-5 mt-4 school-style-page athlete-create-page">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-end gap-3 mb-4">
        <div>
            <span class="payment-eyebrow">Edicion de deportista</span>
            <h1 class="h2 fw-bold mb-2">Editar deportista</h1>
            <p class="text-muted mb-0">Tarjeta de vista previa con motor FIFA 18 Card Engine.</p>
        </div>
        <a href="deportistas" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
    </div>

    <form method="POST" action="editar_deportista&id=<?= urlencode((string)$data->id_deportista) ?>" enctype="multipart/form-data">
        <div class="row g-4 align-items-start">
            <div class="col-lg-5 d-flex justify-content-center">
                <div
                    class="fifa-card"
                    id="playerCard"
                    style="--color1:var(--school-primary-color); --color2:var(--school-secondary-color); --color1-rgb:var(--school-primary-rgb); --color2-rgb:var(--school-secondary-rgb);"
                >
                    <!-- Layer 1: Background (CSS-only) -->
                    <div class="fifa-card__bg" aria-hidden="true"></div>
                    <!-- Layer 2: BackgroundMask -->
                    <img src="Card/assets/BackgroundMask.svg" alt="" class="fifa-card__bg-mask" aria-hidden="true">
                    <!-- Layer 3: DiagonalLines -->
                    <img src="Card/assets/DiagonalLines.svg" alt="" class="fifa-card__diag-lines" aria-hidden="true">
                    <!-- Layer 4: Shine (animated sweep via CSS) -->
                    <img src="Card/assets/Shine.svg" alt="" class="fifa-card__shine" aria-hidden="true">
                    <!-- Layer 5: BottomPanel -->
                    <img src="Card/assets/BottomPanel.svg" alt="" class="fifa-card__bottom-panel" aria-hidden="true">
                    <!-- Layer 6: Frame -->
                    <img src="Card/assets/Frame.svg" alt="" class="fifa-card__frame" aria-hidden="true">
                    <!-- Content -->
                    <div class="fifa-card__stats">
                        <span class="fifa-card__rating">99</span>
                        <span class="fifa-card__position">DC</span>
                    </div>
                    <img src="<?= htmlspecialchars($fotoActual, ENT_QUOTES, 'UTF-8') ?>" alt="Vista previa del deportista" id="previewFoto" class="fifa-card__player">
                    <div class="fifa-card__name" id="previewNombre"><?= htmlspecialchars(trim((string)$data->nombres . ' ' . (string)$data->apellidos) ?: 'Tu nombre', ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="fifa-card__meta">
                        <div id="previewCategoria">Categoria</div>
                        <div id="previewNivel">Nivel</div>
                        <div id="previewGenero">Genero</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-lg-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo Documento</label>
                                <select name="tipo_documento" class="form-control" required>
                                    <option value="CC" <?= $data->tipo_documento === 'CC' ? 'selected' : '' ?>>Cedula de Ciudadania</option>
                                    <option value="TI" <?= $data->tipo_documento === 'TI' ? 'selected' : '' ?>>Tarjeta de Identidad</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Numero Documento</label>
                                <input type="text" name="num_documento" class="form-control" value="<?= htmlspecialchars((string)$data->id_deportista, ENT_QUOTES, 'UTF-8') ?>" maxlength="11" pattern="\d{1,11}" inputmode="numeric" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombres</label>
                                <input type="text" name="nombres" id="nombres" class="form-control" value="<?= htmlspecialchars((string)$data->nombres, ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control" value="<?= htmlspecialchars((string)$data->apellidos, ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" value="<?= htmlspecialchars((string)$data->fecha_nacimiento, ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Genero</label>
                                <select name="genero" id="genero" class="form-control" required>
                                    <option value="Masculino" <?= $data->genero === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                    <option value="Femenino" <?= $data->genero === 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jornada</label>
                                <select name="jornada" class="form-control" required>
                                    <option value="Manana" <?= $data->jornada === 'Manana' ? 'selected' : '' ?>>Manana</option>
                                    <option value="Tarde" <?= $data->jornada === 'Tarde' ? 'selected' : '' ?>>Tarde</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoria</label>
                                <select name="id_categoria" id="categoria" class="form-control" required>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= (int)$c->id_categoria ?>" <?= (int)$data->id_categoria === (int)$c->id_categoria ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string)$c->nombre_cat, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nivel</label>
                                <select name="id_nivel" id="nivel" class="form-control" required>
                                    <?php foreach ($niveles as $n): ?>
                                        <option value="<?= (int)$n->id_nivel ?>" <?= (int)$data->id_nivel === (int)$n->id_nivel ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string)$n->nombre, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Foto</label>
                                <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                            </div>
                            <div class="col-12 mt-3 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary px-4">Actualizar</button>
                                <a href="deportistas" class="btn btn-outline-secondary px-4">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="Card/card-theme.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var nombres = document.getElementById("nombres");
    var apellidos = document.getElementById("apellidos");
    var categoria = document.getElementById("categoria");
    var nivel = document.getElementById("nivel");
    var genero = document.getElementById("genero");
    var foto = document.getElementById("foto");
    var previewNombre = document.getElementById("previewNombre");
    var previewCategoria = document.getElementById("previewCategoria");
    var previewNivel = document.getElementById("previewNivel");
    var previewGenero = document.getElementById("previewGenero");
    var previewFoto = document.getElementById("previewFoto");

    function sync() {
        previewNombre.innerText = (nombres.value + " " + apellidos.value).trim() || "Tu nombre";
        previewCategoria.innerText = categoria.options[categoria.selectedIndex]?.text || "Categoria";
        previewNivel.innerText = nivel.options[nivel.selectedIndex]?.text || "Nivel";
        previewGenero.innerText = genero.value || "Genero";
    }

    [nombres, apellidos, categoria, nivel, genero].forEach(function (el) {
        el.addEventListener("input", sync);
        el.addEventListener("change", sync);
    });

    foto.addEventListener("change", function(e) {
        var file = e.target.files && e.target.files[0];
        if (file) {
            previewFoto.src = URL.createObjectURL(file);
        }
    });

    sync();
});
</script>
