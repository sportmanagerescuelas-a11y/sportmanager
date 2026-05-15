<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Uniforme;

final class UniformesController
{
    private const TYPES = [
        'competencia' => 'Competencia',
        'entrenamiento' => 'Entrenamiento',
        'extra' => 'Extra',
    ];

    private ?Uniforme $model = null;

    public function index(): void
    {
        $this->requireLogin();
        $role = $this->role();
        $userId = $this->userId();

        $this->render('uniformes/index', [
            'uniformes' => $this->model()->allForRole($role, $userId),
            'role' => $role,
            'canCreate' => in_array($role, [2, 3], true),
            'canManage' => $role === 3,
            'message' => $this->messageFromQuery(),
        ]);
    }

    public function create(): void
    {
        $this->requireCanCreate();
        $error = '';
        $formData = $this->emptyFormData();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = $this->payload();
            $error = $this->validate($formData, null);

            if ($error === '' && $this->model()->create($formData)) {
                $this->redirect('index.php?url=uniformes&created=1');
            }

            if ($error === '') {
                $error = $this->model()->lastError() ?: 'No se pudo registrar el uniforme.';
            }
        }

        $this->render('uniformes/form', [
            'athletes' => $this->model()->athletesForAssignment(),
            'types' => self::TYPES,
            'formData' => $formData,
            'error' => $error,
            'isEdit' => false,
        ]);
    }

    public function edit(): void
    {
        $this->requireAdmin();
        $id = $this->uniformIdFromRequest();
        $uniforme = $id > 0 ? $this->model()->findById($id, $this->role(), $this->userId()) : null;
        if (!$uniforme) {
            $this->redirect('index.php?url=uniformes&error=notfound');
        }

        $error = '';
        $formData = $uniforme;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = $this->payload();
            $error = $this->validate($formData, $id);

            if ($error === '' && $this->model()->update($id, $formData)) {
                $this->redirect('index.php?url=uniformes&updated=1');
            }

            if ($error === '') {
                $error = $this->model()->lastError() ?: 'No se pudo actualizar el uniforme.';
            }
        }

        $this->render('uniformes/form', [
            'athletes' => $this->model()->athletesForAssignment($id),
            'types' => self::TYPES,
            'formData' => $formData,
            'error' => $error,
            'isEdit' => true,
            'uniformId' => $id,
        ]);
    }

    public function delete(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?url=uniformes');
        }

        $id = $this->uniformIdFromRequest();
        if ($id <= 0 || !$this->model()->findById($id, $this->role(), $this->userId())) {
            $this->redirect('index.php?url=uniformes&error=notfound');
        }

        if ($this->model()->delete($id)) {
            $this->redirect('index.php?url=uniformes&deleted=1');
        }

        $this->redirect('index.php?url=uniformes&error=delete');
    }

    private function model(): Uniforme
    {
        if (!$this->model instanceof Uniforme) {
            $this->model = new Uniforme();
        }

        return $this->model;
    }

    /**
     * @param array<string,mixed> $data
     */
    private function render(string $view, array $data = []): void
    {
        require APP_PATH . '/views/layout/header.php';
        View::render($view, $data);
        require APP_PATH . '/views/layout/footer.php';
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(): array
    {
        return [
            'numero_camiseta' => (int)($_POST['numero_camiseta'] ?? 0),
            'id_deportista' => (int)($_POST['id_deportista'] ?? 0),
            'tipo_uniforme' => trim((string)($_POST['tipo_uniforme'] ?? '')),
            'nombre_camiseta' => trim((string)($_POST['nombre_camiseta'] ?? '')),
            'descripcion_uniforme' => trim((string)($_POST['descripcion_uniforme'] ?? '')),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function emptyFormData(): array
    {
        return [
            'numero_camiseta' => '',
            'id_deportista' => '',
            'tipo_uniforme' => 'competencia',
            'nombre_camiseta' => '',
            'descripcion_uniforme' => '',
        ];
    }

    /**
     * @param array<string,mixed> $data
     */
    private function validate(array $data, ?int $currentUniformId): string
    {
        $number = (int)($data['numero_camiseta'] ?? 0);
        $athleteId = (int)($data['id_deportista'] ?? 0);
        $type = (string)($data['tipo_uniforme'] ?? '');
        $shirtName = (string)($data['nombre_camiseta'] ?? '');

        if ($number <= 0 || $number > 999) {
            return 'El numero de camiseta debe estar entre 1 y 999.';
        }

        if ($athleteId <= 0 || !$this->model()->athleteExists($athleteId)) {
            return 'Selecciona un deportista valido.';
        }

        if (!array_key_exists($type, self::TYPES)) {
            return 'Selecciona un tipo de uniforme valido.';
        }

        if ($shirtName === '' || strlen($shirtName) > 10) {
            return 'El nombre de camiseta es obligatorio y debe tener maximo 10 caracteres.';
        }

        if ($this->model()->athleteHasUniform($athleteId, $currentUniformId)) {
            return 'Este deportista ya tiene un uniforme asignado.';
        }

        if ($this->model()->numberExistsInAthleteCategory($number, $athleteId, $currentUniformId)) {
            return 'Este numero de camiseta ya esta asignado dentro de la misma categoria.';
        }

        return '';
    }

    private function messageFromQuery(): string
    {
        if (isset($_GET['created'])) {
            return 'Uniforme registrado correctamente.';
        }
        if (isset($_GET['updated'])) {
            return 'Uniforme actualizado correctamente.';
        }
        if (isset($_GET['deleted'])) {
            return 'Uniforme eliminado correctamente.';
        }
        if (isset($_GET['error'])) {
            return match ((string)$_GET['error']) {
                'notfound' => 'No se encontro el uniforme solicitado.',
                'delete' => 'No se pudo eliminar el uniforme.',
                default => 'No se pudo completar la accion.',
            };
        }

        return '';
    }

    private function uniformIdFromRequest(): int
    {
        return (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    }

    private function requireLogin(): void
    {
        if (!isset($_SESSION['usuario'], $_SESSION['id_usuario'])) {
            $this->redirect('index.php?url=login');
        }
    }

    private function requireCanCreate(): void
    {
        $this->requireLogin();
        if (!in_array($this->role(), [2, 3], true)) {
            $this->redirect('index.php?url=uniformes');
        }
    }

    private function requireAdmin(): void
    {
        $this->requireLogin();
        if ($this->role() !== 3) {
            $this->redirect('index.php?url=uniformes');
        }
    }

    private function role(): int
    {
        return (int)($_SESSION['rol'] ?? 0);
    }

    private function userId(): int
    {
        return (int)($_SESSION['id_usuario'] ?? 0);
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }
}

