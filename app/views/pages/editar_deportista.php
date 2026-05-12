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
?>
<br>
<br>
<div class="container mt-5">
    <h2>Editar Deportista</h2>
    <form method="POST" action="index.php?url=editar_deportista&id=<?= urlencode((string)$data->id_deportista) ?>" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-5 d-flex justify-content-center align-items-center mb-4">
                <div class="card-fifa">
                    <img src="assets/img/carta2.png" class="card-bg">
                    <img src="fotos/<?= htmlspecialchars((string)$data->foto) ?>" id="previewFoto" class="player-img">
                    <div class="media">99</div>
                    <div class="pos">DC</div>
                    <div class="nombre" id="previewNombre"><?= htmlspecialchars($data->nombres . ' ' . $data->apellidos) ?></div>
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
                        <label>Tipo Documento</label>
                        <select name="tipo_documento" class="form-control">
                            <option value="CC" <?= $data->tipo_documento === 'CC' ? 'selected' : '' ?>>CC</option>
                            <option value="TI" <?= $data->tipo_documento === 'TI' ? 'selected' : '' ?>>TI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Numero Documento</label>
                        <input type="number" name="num_documento" class="form-control" value="<?= htmlspecialchars((string)$data->id_deportista) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Nombres</label>
                        <input type="text" name="nombres" id="nombres" class="form-control" value="<?= htmlspecialchars((string)$data->nombres) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Apellidos</label>
                        <input type="text" name="apellidos" id="apellidos" class="form-control" value="<?= htmlspecialchars((string)$data->apellidos) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Fecha Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" value="<?= htmlspecialchars((string)$data->fecha_nacimiento) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Genero</label>
                        <select name="genero" id="genero" class="form-control">
                            <option value="Masculino" <?= $data->genero === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                            <option value="Femenino" <?= $data->genero === 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Jornada</label>
                        <select name="jornada" class="form-control">
                            <option <?= $data->jornada === 'Manana' ? 'selected' : '' ?>>Manana</option>
                            <option <?= $data->jornada === 'Tarde' ? 'selected' : '' ?>>Tarde</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Categoria</label>
                        <select name="id_categoria" id="categoria" class="form-control">
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= (int)$c->id_categoria ?>" <?= (int)$data->id_categoria === (int)$c->id_categoria ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)$c->nombre_cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Nivel</label>
                        <select name="id_nivel" id="nivel" class="form-control">
                            <?php foreach ($niveles as $n): ?>
                                <option value="<?= (int)$n->id_nivel ?>" <?= (int)$data->id_nivel === (int)$n->id_nivel ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)$n->nombre) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label>Foto</label>
                        <input type="file" name="foto" id="foto" class="form-control">
                    </div>
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="index.php?url=deportistas" class="btn btn-secondary">Cancelar</a>
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
    const nivel = document.getElementById("nivel");
    const genero = document.getElementById("genero");
    const previewNombre = document.getElementById("previewNombre");
    const previewCategoria = document.getElementById("previewCategoria");
    const previewNivel = document.getElementById("previewNivel");
    const previewGenero = document.getElementById("previewGenero");
    function sync() {
        previewNombre.innerText = (nombres.value + " " + apellidos.value).trim();
        previewCategoria.innerText = categoria.options[categoria.selectedIndex].text;
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
