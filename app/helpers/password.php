<?php

if (!function_exists('sm_password_policy')) {
    /**
     * @return array{pattern:string,min_length:int,requirements:array<int,string>}
     */
    function sm_password_policy(): array
    {
        return [
            'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&._-]).{8,}$/',
            'min_length' => 8,
            'requirements' => [
                'Minimo 8 caracteres',
                'Una letra mayuscula',
                'Una letra minuscula',
                'Un numero',
                'Un caracter especial (@$!%*?&._-)',
            ],
        ];
    }
}

if (!function_exists('sm_password_is_valid')) {
    function sm_password_is_valid(string $password): bool
    {
        $policy = sm_password_policy();
        return preg_match($policy['pattern'], $password) === 1;
    }
}

if (!function_exists('sm_password_requirement_status')) {
    /**
     * @return array{length:bool,upper:bool,lower:bool,number:bool,special:bool}
     */
    function sm_password_requirement_status(string $password): array
    {
        return [
            'length' => strlen($password) >= 8,
            'upper' => preg_match('/[A-Z]/', $password) === 1,
            'lower' => preg_match('/[a-z]/', $password) === 1,
            'number' => preg_match('/\d/', $password) === 1,
            'special' => preg_match('/[@$!%*?&._\-]/', $password) === 1,
        ];
    }
}
