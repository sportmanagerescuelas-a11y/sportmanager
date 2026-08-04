# Project Context

## What this project is

Sport Manager is a PHP-based sports management system focused on school organizations. The application supports school branding, athlete registration, event management, payments, invoices, uniforms, attendance, and user administration.

## Current stack

- PHP 8.x
- MySQL / MariaDB
- Bootstrap 5
- Vanilla JavaScript
- Composer packages:
  - `phpmailer/phpmailer`
  - `phpoffice/phpspreadsheet`
  - `mpdf/mpdf`

## Main entry points

- `index.php` handles the main routing.
- `app/views/layout/header.php` and `app/views/layout/footer.php` render the shell.
- `app/bootstrap.php` loads shared helpers.

## Important project areas

- `app/controllers`
  - Contains the main application flows.
  - `PagesController.php` is a central controller for dashboard, users, schools, athletes, and forms.
  - `AuthController.php` handles login and account access flows.
- `app/models`
  - Contains DB access and query logic.
  - `PagesModel.php` provides school, user, athlete, event, and payment queries.
- `app/views`
  - Contains page templates.
  - `app/views/pages/admin_usuarios.php` shows pending and approved users.
  - `app/views/pages/crear_deportista.php` and `editar_deportista.php` use the same gold card component.
- `app/helpers`
  - `ui.php` contains shared rendering helpers, CSRF helpers, and category helpers.
- `Card`
  - Contains the gold athlete card styling.
- `assets/js`
  - `gold-card-preview.js` keeps the athlete card preview in sync with the form.

## Current business rules

- Athlete category is derived from date of birth.
  - Example: birth year 2020 maps to `sub-7`.
- The athlete card uses a shared component and is reused in create and edit views.
- The card frame and banner are fixed gold.
- The only card area that varies by school theme is the background color palette.
- The header branding should show the school name for authenticated users who belong to a school.

## School branding behavior

- The app reads school theme data from the authenticated user relation.
- The school shield is exposed as a CSS variable and reused in the UI.
- For school users, the navbar branding should display the school name instead of the generic app name.

## User administration

- `app/views/pages/admin_usuarios.php` displays users and school names.
- `PagesModel::usersBySchool()` joins `usuarios` with `escuelas` and returns `nombre_escuela`.
- The view should still fall back to the current school name if a row is missing the school label.

## Athlete registration flow

- `app/controllers/PagesController.php::createAthlete()` and `editAthlete()` validate form input.
- The category is calculated server-side from `fecha_nacimiento`.
- The preview card is updated client-side by `assets/js/gold-card-preview.js`.

## Notes for future changes

- Prefer shared helpers in `app/helpers/ui.php` instead of duplicating UI logic inside views.
- Keep school-specific colors and shield handling centralized in the layout/header layer.
- When changing athlete card layout, update both CSS and preview JS together.
- When changing category rules, update both backend validation and preview logic.

