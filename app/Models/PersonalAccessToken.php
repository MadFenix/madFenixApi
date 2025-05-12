<?php
namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    // Forzar el uso de la conexión tenant
    protected $connection = 'tenant';

    // Opcional: si necesitas lógica dinámica, puedes sobrescribir getConnectionName()
    // public function getConnectionName()
    // {
    //     return 'tenant';
    // }
}
