# Guía de despliegue - SportManager

## Descripción
Este documento describe el procedimiento recomendado para desplegar el proyecto SportManager en un entorno local o de servidor, considerando Apache, PHP, MySQL y la integración con PayU.

## Requisitos previos

### Software requerido
- XAMPP o un servidor Apache con PHP y MySQL
- PHP 8.x compatible con el proyecto
- Composer
- Git
- Navegador web moderno

### Dependencias del proyecto
- `composer.json` y `composer.lock` deben estar presentes
- La carpeta `vendor/` debe estar instalada correctamente
- La base de datos debe importarse desde `Database/sportmanager.sql`

## Preparación del entorno

1. Clonar el proyecto en el directorio web del servidor.
2. Verificar que la estructura principal exista:
   - `index.php`
   - `app/`
   - `config/`
   - `assets/`
   - `Database/`
   - `vendor/`
3. Confirmar permisos de lectura y escritura sobre:
   - `storage/`
   - `uploads/`
   - `fotos/`
   - `storage/payment_receipts/`

## Configuración de la base de datos

1. Crear la base de datos en MySQL.
2. Importar el archivo:
   - `Database/sportmanager.sql`
3. Revisar la configuración de conexión en:
   - `config/conexion.php`
   - `config/env.php`
4. Ajustar el host, usuario, contraseña y nombre de la base de datos según el entorno.

## Configuración de PHP y Apache

### Recomendaciones
- Habilitar el módulo de `mod_rewrite` si se usa routing amigable.
- Configurar el `DocumentRoot` apuntando al proyecto.
- Verificar que `allow_url_fopen` y extensiones requeridas de PHP estén habilitadas.
- Revisar el valor de `display_errors` para entornos de desarrollo y producción.

### Variables de entorno
Si el proyecto usa variables de entorno o configuración específica, revisar:
- `config/env.php`
- `config/payu.php`

## Configuración de PayU

1. Verificar las credenciales de PayU en el archivo de configuración correspondiente.
2. Confirmar el entorno de pruebas o producción según corresponda.
3. Validar que las URLs de respuesta y notificación estén configuradas correctamente.

## Instalación de dependencias

Ejecutar desde la raíz del proyecto:

```bash
composer install
```

Si se actualiza el proyecto, también puede requerirse:

```bash
composer update
```

## Ejecución local

1. Iniciar Apache y MySQL en XAMPP.
2. Abrir la URL del proyecto en el navegador, por ejemplo:

```text
http://localhost/sportmanager
```

3. Verificar que el login y las rutas principales funcionen correctamente.

## Verificación post-despliegue

Revisar lo siguiente:
- Conexión a la base de datos
- Inicio de sesión
- Carga de assets y estilos
- Envío de pagos y notificaciones
- Generación de facturas o PDFs, si aplica
- Acceso a archivos en `storage/` y `uploads/`

## Recomendaciones finales
- No subir credenciales reales al repositorio.
- Mantener copias de seguridad de la base de datos antes de cada cambio importante.
- Usar un entorno de pruebas antes de liberar a producción.
- Revisar logs de PHP y Apache en caso de errores.

## Contacto / mantenimiento
El proyecto debe mantenerse con control de versiones y revisión periódica de configuraciones sensibles como conexión a base de datos, PayU y archivos de sesión.
