# Contexto del proyecto

## Que es este proyecto

Sport Manager es un sistema web en PHP enfocado en la gestion deportiva de escuelas. La aplicacion maneja branding por escuela, registro de deportistas, gestion de eventos, pagos, facturas, uniformes, asistencia y administracion de usuarios.

## Stack actual

- PHP 8.x
- MySQL / MariaDB
- Bootstrap 5
- JavaScript nativo
- Paquetes Composer:
  - `phpmailer/phpmailer`
  - `phpoffice/phpspreadsheet`
  - `mpdf/mpdf`

## Puntos de entrada principales

- `index.php` maneja el enrutamiento principal.
- `app/views/layout/header.php` y `app/views/layout/footer.php` renderizan la estructura general.
- `app/bootstrap.php` carga los helpers compartidos.

## Areas importantes del proyecto

- `app/controllers`
  - Contiene los flujos principales de la aplicacion.
  - `PagesController.php` es el controlador central para dashboard, usuarios, escuelas, deportistas y formularios.
  - `AuthController.php` maneja inicio de sesion y acceso a cuentas.
- `app/models`
  - Contiene acceso a base de datos y logica de consultas.
  - `PagesModel.php` expone consultas para escuelas, usuarios, deportistas, eventos y pagos.
- `app/views`
  - Contiene las plantillas de pagina.
  - `app/views/pages/admin_usuarios.php` muestra usuarios pendientes y aprobados.
  - `app/views/pages/crear_deportista.php` y `editar_deportista.php` usan el mismo componente visual de tarjeta.
- `app/helpers`
  - `ui.php` contiene helpers compartidos de renderizado, CSRF y categorias.
- `Card`
  - Contiene el estilo visual de la tarjeta dorada del deportista.
- `assets/js`
  - `gold-card-preview.js` sincroniza la vista previa de la tarjeta con el formulario.

## Reglas de negocio vigentes

- La categoria del deportista se deriva de la fecha de nacimiento.
  - Ejemplo: un nacimiento en 2020 corresponde a `sub-7`.
- La tarjeta del deportista usa un componente compartido y se reutiliza en crear y editar.
- El marco y el banner de la tarjeta son dorados y fijos.
- La unica parte de la tarjeta que cambia segun la escuela es la paleta de fondo.
- El header debe mostrar el nombre de la escuela cuando el usuario autenticado pertenece a una escuela.

## Comportamiento del branding de escuela

- La aplicacion lee los datos visuales de la escuela desde la relacion del usuario autenticado.
- El escudo de la escuela se expone como variable CSS y se reutiliza en la interfaz.
- Para usuarios de escuela, el navbar debe mostrar el nombre de la escuela en lugar del nombre generico de la aplicacion.

## Administracion de usuarios

- `app/views/pages/admin_usuarios.php` muestra usuarios y nombre de escuela.
- `PagesModel::usersBySchool()` une `usuarios` con `escuelas` y devuelve `nombre_escuela`.
- La vista conserva un respaldo con el nombre de la escuela actual si alguna fila viene incompleta.

## Flujo de registro de deportistas

- `app/controllers/PagesController.php::createAthlete()` y `editAthlete()` validan los datos del formulario.
- La categoria se calcula del lado del servidor a partir de `fecha_nacimiento`.
- La vista previa de la tarjeta se actualiza del lado del cliente con `assets/js/gold-card-preview.js`.

## Notas para cambios futuros

- Preferir helpers compartidos en `app/helpers/ui.php` en lugar de duplicar logica visual en las vistas.
- Mantener centralizado el manejo del color y escudo de escuela en la capa de layout/header.
- Cuando cambie el disenio de la tarjeta del deportista, actualizar CSS y JS de vista previa al mismo tiempo.
- Cuando cambie la regla de categorias, actualizar tanto la validacion del backend como la vista previa.
