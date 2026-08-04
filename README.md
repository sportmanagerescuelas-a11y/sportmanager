# Sport Manager

Sistema web para gestion deportiva por escuelas. Permite registrar y administrar escuelas, usuarios, deportistas, eventos, pagos, facturas, uniformes y reportes desde una sola aplicacion PHP.

## Resumen

- Backend en PHP nativo con acceso a base de datos MySQL/MariaDB.
- Frontend con HTML, CSS, Bootstrap y JavaScript ligero.
- Soporte para multiples roles de usuario y tema visual por escuela.
- Integracion con correo, exportacion de documentos y generacion de PDF.

## Caracteristicas principales

- Registro e inicio de sesion de usuarios.
- Creacion y administracion de escuelas.
- Panel con tema visual basado en los colores y escudo de cada escuela.
- Gestion de deportistas con formulario, vista previa y carga de foto.
- Categoria automatica segun fecha de nacimiento.
- Gestion de eventos, inscripciones y pagos.
- Generacion y consulta de facturas.
- Gestion de uniformes y asistencia.
- Listado y aprobacion de usuarios por escuela o por administrador global.

## Estructura general

- `index.php`: punto de entrada y enrutador principal.
- `app/controllers`: controladores de negocio y flujo de pantallas.
- `app/models`: consultas y reglas de acceso a datos.
- `app/views`: vistas y plantillas HTML.
- `app/helpers`: utilidades compartidas de UI, CSRF y renderizado.
- `assets`: estilos, scripts e imagenes publicas.
- `Card`: estilos y componentes visuales de la tarjeta de deportista.
- `Database/sportmanager.sql`: dump de base de datos.
- `fotos`: archivos subidos por usuarios y deportistas.

## Requisitos

- PHP 8.x o superior.
- MySQL o MariaDB.
- Composer.
- Servidor web compatible con PHP, por ejemplo XAMPP.

## Instalacion

1. Clona o copia el proyecto en tu servidor local.
2. Instala dependencias con Composer:

```bash
composer install
```

3. Crea la base de datos `sportmanager`.
4. Importa `Database/sportmanager.sql`.
5. Ajusta tus credenciales en `.env` si aplica.
6. Verifica que la carpeta `fotos` tenga permisos de escritura.

## Ejecucion local

Si usas XAMPP, coloca el proyecto dentro de `htdocs` y abre:

```text
http://localhost/sportmanager
```

## Convenciones importantes

- El nombre, colores y escudo de la escuela se usan en el header y en varias vistas.
- La categoria del deportista se calcula segun la fecha de nacimiento.
- La tarjeta visual de deportista reutiliza un unico componente compartido.
- No mezclar logica de vista con reglas de negocio cuando exista un helper o modelo para eso.

## Dependencias

- `phpmailer/phpmailer`
- `phpoffice/phpspreadsheet`
- `mpdf/mpdf`

## Notas para desarrollo

- Revisar siempre `app/helpers/ui.php` antes de crear componentes visuales nuevos.
- Revisar `app/models/PagesModel.php` y `app/controllers/PagesController.php` para cambios de flujo.
- Las vistas de usuarios de escuela usan `nombre_escuela` como dato principal.

