# Copilot Instructions for AI Agents

## Arquitectura General
- Proyecto PHP bajo el modelo MVC estrictamente:
    - `models/`: Lógica de datos y acceso a base de datos.
    - `controllers/`: Lógica de negocio y controladores de rutas.
    - `views/`: Vistas, fragmentos y layouts (ej: `layout/header.php`, `layout/footer.php`).
    - `config/conexion.php`: Configuración de conexión a la base de datos.
    - `assets/`: Recursos estáticos (CSS, JS, imágenes).
    - `uploads/`: Almacenamiento de archivos subidos por usuarios.
    - `lib/`: Librerías externas gestionadas mediante Composer (`composer.json`, `composer.lock`).
- El archivo de entrada principal es `index.php`.
- El proyecto se ejecuta en entorno local usando XAMPP (Apache + MySQL).

## Flujos y Convenciones
- Las rutas suelen ser gestionadas por controladores en `controllers/`.
- La vista se compone usando layouts en `views/layout/`.
- La conexión a la base de datos se centraliza en `config/conexion.php` y es reutilizada por los modelos.
- Los assets se referencian desde las vistas usando rutas relativas a `assets/`.

- Convención de JavaScript: evitar scripts inline en las vistas. Todos los scripts utilizados por la página principal (index) deben agruparse en `assets/js/indexcontroller.js`. Para scripts específicos de una vista, crear archivos con nombre explícito en `assets/js/` (por ejemplo `assets/js/header.js`, `assets/js/login.js`) y enlazarlos desde la vista correspondiente. Esto mantiene el HTML limpio y facilita el cacheo y el versionado.
    - Por módulo: crear un "controller" de JavaScript por módulo siguiendo la convención `modulocontroller.js` en `assets/js/`. Ejemplos: `usuariocontroller.js` para el módulo Usuario, `productocontroller.js` para el módulo Producto. Agrupa en cada archivo la lógica DOM/eventos y llamadas AJAX relacionadas con ese módulo.

- Responsividad obligatoria: cualquier cambio en vistas debe ser responsivo. Prioriza mobile-first, prueba en anchos comunes (360px, 768px, 1024px) y usa utilidades de Bootstrap o media queries en `assets/css/style.css`. Documenta en la misma vista si hay requisitos especiales de responsive.

## Ejemplo de flujo típico
1. Petición HTTP llega a `index.php`.
2. Se enruta a un controlador en `controllers/`.
3. El controlador usa modelos de `models/` para acceder a datos.
4. El controlador carga una vista desde `views/`, usando layouts.

## Pruebas y Debug
- No hay scripts de pruebas automatizadas documentados.
- Para debug, se recomienda usar `var_dump()` y revisar el flujo en los controladores.

## Convenciones específicas
- Los nombres de archivos y clases siguen el patrón PascalCase o camelCase según contexto.
- Los fragmentos de vista comunes están en `views/layout/`.
- Los controladores suelen incluir la lógica de validación y redirección.

## Dependencias externas
- Requiere un servidor PHP (ej: XAMPP) y acceso a MySQL.
- No se detectan frameworks externos (Laravel, Symfony, etc.).

## Archivos clave
- `index.php`: Punto de entrada y ruteo inicial.
- `config/conexion.php`: Configuración de base de datos.
- `controllers/`, `models/`, `views/`: Estructura MVC.
- `assets/`: Recursos estáticos.

## Ejemplo de patrón controlador
```php
// controllers/UsuarioController.php
require_once '../models/Usuario.php';
class UsuarioController {
    public function login($user, $pass) {
        $usuario = Usuario::findByCredentials($user, $pass);
        if ($usuario) {
            // ... lógica de sesión ...
        } else {
            // ... manejo de error ...
        }
    }
}
```

---

Ajusta y amplía estas instrucciones si detectas nuevos patrones o convenciones en el código.