<?php
$viewData = get_defined_vars();
$categorias = is_array($viewData['categorias'] ?? null) ? $viewData['categorias'] : [];
$niveles = is_array($viewData['niveles'] ?? null) ? $viewData['niveles'] : [];
$error = $viewData['error'] ?? null;
?>
<br>
<br>
<br>
<div class="container mt-4">
    <h2>Registrar Deportista</h2>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST" action="index.php?url=crear_deportista" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-5 d-flex justify-content-center align-items-center mb-4">
                <div class="card-fifa">
                    <img src="assets/img/carta2.png" class="card-bg">
                    <img src="fotos/default.png" id="previewFoto" class="player-img">
                    <div class="media">99</div>
                    <div class="pos">DC</div>
                    <div class="nombre" id="previewNombre">Tu nombre</div>
                    <div class="extra-info">
                        <div id="previewCategoria">Categoria</div>
                        <div id="previewNivel">Nivel</div>
                        <div id="previewGenero">Genero</div>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tipo Documento</label>
                        <select name="tipo_documento" class="form-control" required>
                            <option value="CC">Cedula de Ciudadania</option>
                            <option value="TI">Tarjeta de Identidad</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Numero Documento</label>
                        <input type="number" name="id_deportista" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nombres</label>
                        <input type="text" name="nombres" id="nombres" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Genero</label>
                        <select name="genero" id="genero" class="form-control">
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jornada</label>
                        <select name="jornada" class="form-control">
                            <option>Manana</option>
                            <option>Tarde</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Categoria</label>
                        <select id="categoria" class="form-control">
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= (int)$c->id_categoria ?>"><?= htmlspecialchars((string)$c->nombre_cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="id_categoria" id="categoria_hidden">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nivel</label>
                        <select name="id_nivel" id="nivel" class="form-control">
                            <?php foreach ($niveles as $n): ?>
                                <option value="<?= (int)$n->id_nivel ?>"><?= htmlspecialchars((string)$n->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Foto</label>
                        <input type="file" name="foto" id="foto" class="form-control">
                    </div>
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="index.php?url=deportistas" class="btn btn-secondary">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const nombres = document.getElementById("nombres");
    const apellidos = document.getElementById("apellidos");
    const categoria = document.getElementById("categoria");
    const categoriaHidden = document.getElementById("categoria_hidden");
    const nivel = document.getElementById("nivel");
    const genero = document.getElementById("genero");
    const previewNombre = document.getElementById("previewNombre");
    const previewCategoria = document.getElementById("previewCategoria");
    const previewNivel = document.getElementById("previewNivel");
    const previewGenero = document.getElementById("previewGenero");

    function sync() {
        previewNombre.innerText = (nombres.value + " " + apellidos.value).trim() || "Tu nombre";
        previewCategoria.innerText = categoria.options[categoria.selectedIndex].text;
        categoriaHidden.value = categoria.value;
        previewNivel.innerText = nivel.options[nivel.selectedIndex].text;
        previewGenero.innerText = genero.value;
    }

    [nombres, apellidos, categoria, nivel, genero].forEach(el => el.addEventListener("input", sync));
    document.getElementById("foto").addEventListener("change", function(e) {
        if (e.target.files[0]) document.getElementById("previewFoto").src = URL.createObjectURL(e.target.files[0]);
    });
    sync();
});
</script>
<br>
