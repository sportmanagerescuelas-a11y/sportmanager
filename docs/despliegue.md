# Manual de despliegue para SportManager

## 1. Descripción

SportManager es una aplicación web PHP MVC para gestionar escuelas deportivas, deportistas, pagos, facturación y eventos. Se despliega normalmente sobre Apache + PHP + MySQL.

## 2. Requisitos mínimos

- PHP 8.x o superior
- Apache
- MySQL o MariaDB
- Composer
- Extensiones PHP mínimas:
  - `pdo_mysql`
  - `openssl`
  - `mbstring`
  - `fileinfo`
  - `zip`
  - `xml`
  - `gd` o `imagick` (recomendado para generación de imágenes, PDF o Excel)

## 3. Preparación del entorno local con XAMPP

1. Copia el proyecto dentro de `htdocs`:
   - `c:\xampp\htdocs\sportmanager`
2. Asegúrate de que Apache esté detenido mientras ajustas la configuración.
3. Activa `mod_rewrite` en Apache si no está habilitado.
4. Activa las extensiones PHP necesarias en `php.ini`.
5. Inicia Apache y MySQL desde el panel de XAMPP.

## 4. Instalación de dependencias

Desde la raíz del proyecto:

```bash
cd c:\xampp\htdocs\sportmanager
composer install
```

Esto descarga los paquetes usados por PHPMailer, PhpSpreadsheet, MPDF y otras librerías.

## 5. Creación de la base de datos

1. Abre phpMyAdmin o usa la línea de comandos de MySQL.
2. Crea la base de datos `sportmanager` o el nombre que prefieras.
3. Importa el script SQL:

```sql
SOURCE c:/xampp/htdocs/sportmanager/Database/sportmanager.sql;
```

4. Si cambias el nombre de la base de datos, actualiza la configuración en `config/conexion.php`.

## 6. Configuración de la conexión a base de datos

Archivo principal:

- `config/conexion.php`

Configuración por defecto:

- host: `localhost`
- usuario: `root`
- contraseña: vacía
- base: `sportmanager`

Si tu entorno es distinto, ajusta esos valores.

## 7. Configuración de PayU

Archivo principal:

- `config/payu.php`

Parámetros relevantes:

- `apiKey`
- `apiLogin`
- `merchantId`
- `accountId`
- `isTest` (`true` para sandbox, `false` para producción)
- `returnUrlBase`

> Importante: no compartas credenciales reales en repositorios públicos. En producción, usa un almacenamiento seguro y sólo actualiza los valores necesarios en `config/payu.php` o con variables de entorno.

## 8. Rutas amigables y Apache

El proyecto incluye un `.htaccess` en la raíz para:

- proteger carpetas internas,
- permitir rutas limpias,
- redirigir todo a `index.php`.

Si despliegas en un subdirectorio distinto de `/sportmanager/`, ajusta `RewriteBase` en `.htaccess` y el valor de `returnUrlBase` en `config/payu.php`.

### Requisitos de Apache

- `AllowOverride All` en el virtual host o directorio.
- `mod_rewrite` habilitado.

Si no puedes usar `.htaccess`, la aplicación también puede funcionar con URLs tipo `index.php?url=...`, pero las rutas amigables no estarán disponibles.

## 9. Permisos de archivos y carpetas

Asegura que las carpetas de almacenamiento sean escribibles por el usuario de Apache/PHP:

- `storage/sessions`
- `storage/payment_receipts`
- `uploads` (si se usa)

En Windows, el usuario de Apache debe tener permisos de escritura sobre esas rutas.

## 10. Desactivar `display_errors` en producción

En `app/bootstrap.php` la opción `display_errors` viene habilitada por defecto. Para un ambiente real, cambia esto en `php.ini` o ajusta la línea:

```php
ini_set('display_errors', '1');
```

Se recomienda usar `display_errors = Off` en producción y conservar el manejo de errores en logs controlados.

## 11. Arranque de la aplicación

Abre en el navegador:

```text
http://localhost/sportmanager/
```

En producción, la raíz del sitio debe apuntar al directorio `sportmanager` o adaptar `.htaccess` y `RewriteBase` según la ruta.

## 12. Verificación y pruebas antes de despliegue

1. Registra un usuario o inicia sesión con uno existente.
2. Prueba la creación de deportistas, eventos y pagos según los permisos del rol.
3. Verifica que los comprobantes, facturas y sesiones se manejen correctamente.
4. Revisa el flujo de rollback y respaldo antes de lanzar cambios en producción.

## 13. Recomendaciones de operación

- Tener respaldo completo de la base de datos antes del despliegue.
- Definir el tipo de rollback y el tiempo estimado para recuperación.
- Mantener un plan de revisión del soporte y validación post-despliegue.
- Documentar variables de entorno y su paso entre GitHub y el entorno local.
- Revisar los servicios y reinicios necesarios antes de aplicar cambios productivos.

3. Prueba un pago en sandbox con PayU si `isTest` está en `true`.

13. Problemas comunes

- `503 Fuera de juego`: No se puede conectar a la base de datos.

- Verifica `config/conexion.php` y credenciales MySQL.

- Error 500 o páginas en blanco:

- Revisa logs de Apache/PHP.

- Asegura que `vendor/` esté presente tras `composer install`.

- Rutas amigables no cargan:

- Comprueba `mod_rewrite` y `.htaccess`.

- Asegura `RewriteBase` correcto.

- Sesiones no persisten:

- Verifica permisos de `storage/sessions`

14. Notas adicionales

- `config/env.php` carga un fichero `.env` si existe, pero actualmente el proyecto no consume variables de entorno para la conexión DB. Puedes extender esta funcionalidad si deseas separar configuración del código.

- El documento base de la aplicación está en `index.php`; allí se controlan las rutas y se cargan los controladores.

15. Checklist rápido

- [ ] Apache con `mod_rewrite` activo

- [ ] MySQL disponible con DB importada

- [ ] `composer install` ejecutado

- [ ] `config/conexion.php` actualizado con credenciales

- [ ] `config/payu.php` con credenciales correctas y `returnUrlBase`

- [ ] `storage/` y `uploads/` con permisos de escritura

- [ ] `.htaccess` con `RewriteBase` correcto para el subdirectorio

- [ ] `display_errors` desactivado en producción

---

Este manual está diseñado para un despliegue local en XAMPP y también funciona como guía para migrar a un servidor Apache/PHP estándar.